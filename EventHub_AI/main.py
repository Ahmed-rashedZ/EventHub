"""
==========================================================================
 EventHub AI Microservice — FastAPI Application
==========================================================================
 Purpose : Expose REST endpoints for the EventHub Laravel backend to:
             1. POST /predict   – Predict attendance for an upcoming event
             2. POST /retrain   – Push a completed event, append to CSV,
                                  and retrain the model for continuous learning
             3. GET  /health    – Simple health-check

 Run     : uvicorn main:app --host 0.0.0.0 --port 8000 --reload
==========================================================================
"""

import os
import threading
import pandas as pd
import joblib
import numpy as np
from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel, Field
from typing import Literal


# ---------------------------------------------------------------------------
#  Configuration
# ---------------------------------------------------------------------------

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
DATASET_PATH = os.path.join(BASE_DIR, "synthetic_events_dataset.csv")
MODEL_DIR = os.path.join(BASE_DIR, "model")
MODEL_PATH = os.path.join(MODEL_DIR, "linear_regression_model.joblib")
COLUMNS_PATH = os.path.join(MODEL_DIR, "model_columns.joblib")


# ---------------------------------------------------------------------------
#  FastAPI app
# ---------------------------------------------------------------------------

app = FastAPI(
    title="EventHub AI — Attendance Predictor",
    description=(
        "A Linear-Regression-based microservice that predicts actual "
        "attendance for upcoming events and supports continuous learning "
        "through a retrain endpoint."
    ),
    version="1.0.0",
)

# Allow requests from the Laravel backend (adjust origins in production)
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)


# ---------------------------------------------------------------------------
#  In-memory model store (thread-safe via lock)
# ---------------------------------------------------------------------------

_model = None
_columns = None
_lock = threading.Lock()


def _load_model() -> None:
    """Load the trained model & column schema from disk into memory."""
    global _model, _columns
    if not os.path.exists(MODEL_PATH) or not os.path.exists(COLUMNS_PATH):
        raise FileNotFoundError(
            "Trained model not found. Run `python train.py` first."
        )
    _model = joblib.load(MODEL_PATH)
    _columns = joblib.load(COLUMNS_PATH)


@app.on_event("startup")
def startup_event() -> None:
    """Load model into memory when the server starts."""
    try:
        _load_model()
        print("[INFO] Model loaded into memory. ✅")
    except FileNotFoundError as exc:
        print(f"[WARNING] {exc}")


# ---------------------------------------------------------------------------
#  Pydantic schemas
# ---------------------------------------------------------------------------

# Valid event types based on the dataset
EventType = Literal[
    "Exhibition", "Workshop", "Entertainment", "Conference",
    "Seminar", "Meeting", "Festival", "Course"
]

TimePeriod = Literal["Morning", "Evening"]


class PredictRequest(BaseModel):
    """Schema for the /predict endpoint.
    
    NOTE: Proposed_Capacity is NOT included here.
    The model predicts attendance from event characteristics alone,
    so the manager can use the prediction to decide capacity.
    """
    event_type: EventType = Field(..., alias="Event_Type", description="Type of the event")
    total_days: int = Field(..., alias="Total_Days", ge=1, description="Duration in days")
    includes_weekend: int = Field(..., alias="Includes_Weekend", ge=0, le=1, description="1 if includes weekend, else 0")
    time_period: TimePeriod = Field(..., alias="Time_Period", description="Morning or Evening")

    model_config = {"populate_by_name": True}


class RetrainRequest(BaseModel):
    """Schema for the /retrain endpoint — same fields + actual attendance."""
    event_type: EventType = Field(..., alias="Event_Type", description="Type of the event")
    total_days: int = Field(..., alias="Total_Days", ge=1, description="Duration in days")
    includes_weekend: int = Field(..., alias="Includes_Weekend", ge=0, le=1, description="1 if includes weekend, else 0")
    time_period: TimePeriod = Field(..., alias="Time_Period", description="Morning or Evening")
    actual_attendance: int = Field(..., alias="Actual_Attendance", ge=0, description="Real attendance count")

    model_config = {"populate_by_name": True}


# ---------------------------------------------------------------------------
#  Helper: preprocess a single input row to match training schema
# ---------------------------------------------------------------------------

def _prepare_features(data: PredictRequest) -> pd.DataFrame:
    """
    Convert a single prediction request into a one-row DataFrame whose
    columns exactly match the model's training columns.
    """
    # 1. Initialize all columns to 0.0 based on training schema
    row = {col: 0.0 for col in _columns}

    # 2. Set numerical features directly
    row["Total_Days"] = float(data.total_days)
    row["Includes_Weekend"] = float(data.includes_weekend)

    # 3. Handle one-hot encoding for Event_Type
    # (Conference is the base dropped column since it's alphabetically first)
    event_col = f"Event_Type_{data.event_type}"
    if event_col in row:
        row[event_col] = 1.0

    # 4. Handle one-hot encoding for Time_Period
    # (Evening is the base dropped column since it's alphabetically first)
    period_col = f"Time_Period_{data.time_period}"
    if period_col in row:
        row[period_col] = 1.0

    # Return as a single-row DataFrame
    return pd.DataFrame([row])


# ---------------------------------------------------------------------------
#  Helper: retrain the model (runs inside lock)
# ---------------------------------------------------------------------------

def _retrain() -> dict:
    """Reload the CSV, retrain the model, and swap it into memory."""
    global _model, _columns

    from sklearn.linear_model import LinearRegression

    df = pd.read_csv(DATASET_PATH)
    y = np.log1p(df["Actual_Attendance"].copy())
    
    cols_to_drop = ["Actual_Attendance"]
    if "Proposed_Capacity" in df.columns:
        cols_to_drop.append("Proposed_Capacity")
    X = df.drop(columns=cols_to_drop)
    
    X = pd.get_dummies(X, columns=["Event_Type", "Time_Period"], drop_first=True)
    X = X.astype(float)

    model = LinearRegression()
    model.fit(X, y)

    # Persist to disk
    os.makedirs(MODEL_DIR, exist_ok=True)
    joblib.dump(model, MODEL_PATH)
    joblib.dump(list(X.columns), COLUMNS_PATH)

    # Hot-swap in memory
    _model = model
    _columns = list(X.columns)

    return {"rows": len(df), "features": _columns}


# ---------------------------------------------------------------------------
#  Endpoints
# ---------------------------------------------------------------------------

@app.get("/health", tags=["System"])
def health_check():
    """Simple health-check endpoint."""
    return {
        "status": "healthy",
        "model_loaded": _model is not None,
    }


@app.post("/predict", tags=["Prediction"])
def predict_attendance(payload: PredictRequest):
    """
    Predict the actual attendance for an upcoming event.

    **Example JSON body:**
    ```json
    {
        "Event_Type": "Exhibition",
        "Total_Days": 4,
        "Includes_Weekend": 1,
        "Time_Period": "Morning",
        "Proposed_Capacity": 10000
    }
    ```
    """
    if _model is None:
        raise HTTPException(
            status_code=503,
            detail="Model not loaded. Run `python train.py` first.",
        )

    features = _prepare_features(payload)

    with _lock:
        prediction_log = _model.predict(features)[0]

    # Convert back from log scale (np.expm1 is inverse of np.log1p)
    prediction = np.expm1(prediction_log)

    # Round to the nearest integer — attendance is a whole number
    predicted_attendance = max(0, round(prediction))

    return {
        "status": "success",
        "predicted_attendance": predicted_attendance,
        "input": {
            "Event_Type": payload.event_type,
            "Total_Days": payload.total_days,
            "Includes_Weekend": payload.includes_weekend,
            "Time_Period": payload.time_period,
        },
    }


@app.post("/retrain", tags=["Continuous Learning"])
def retrain_model(payload: RetrainRequest):
    """
    Push a completed event (with real attendance) to the dataset.
    Appends the row to `events_dataset.csv` and retrains the model.

    **Example JSON body:**
    ```json
    {
        "Event_Type": "Workshop",
        "Total_Days": 2,
        "Includes_Weekend": 0,
        "Time_Period": "Morning",
        "Actual_Attendance": 55
    }
    ```
    """
    # 1. Append the new event to the CSV file
    new_row = {
        "Event_Type": payload.event_type,
        "Total_Days": payload.total_days,
        "Includes_Weekend": payload.includes_weekend,
        "Time_Period": payload.time_period,
        "Actual_Attendance": payload.actual_attendance,
    }
    new_df = pd.DataFrame([new_row])
    new_df.to_csv(DATASET_PATH, mode="a", header=False, index=False)

    # 2. Retrain the model with the updated dataset
    with _lock:
        info = _retrain()

    return {
        "status": "success",
        "message": "New event appended and model retrained successfully.",
        "dataset_rows": info["rows"],
        "model_features": info["features"],
    }
