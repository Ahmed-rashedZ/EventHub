"""
==========================================================================
 EventHub AI Microservice — FastAPI Application (v2 — Gradient Boosting)
==========================================================================
 Endpoints:
   1. POST /predict                – Predict attendance + confidence range
   2. POST /retrain                – Append event data & retrain model
   3. POST /generate-description   – AI-generated event description (Gemini 2.0 Flash)
   4. GET  /health                 – Health-check

 Run: uvicorn main:app --host 0.0.0.0 --port 8001 --reload
==========================================================================
"""

import os
import threading
from dotenv import load_dotenv

# Load environment variables from .env file
load_dotenv()
import pandas as pd
import joblib
import numpy as np
from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel, Field
from typing import Literal, Optional
import httpx


# ---------------------------------------------------------------------------
#  Configuration
# ---------------------------------------------------------------------------

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
DATASET_PATH = os.path.join(BASE_DIR, "synthetic_events_dataset.csv")
MODEL_DIR = os.path.join(BASE_DIR, "model")

POINT_MODEL_PATH = os.path.join(MODEL_DIR, "gb_model.joblib")
LOWER_MODEL_PATH = os.path.join(MODEL_DIR, "gb_lower.joblib")
UPPER_MODEL_PATH = os.path.join(MODEL_DIR, "gb_upper.joblib")
COLUMNS_PATH = os.path.join(MODEL_DIR, "model_columns.joblib")


# ---------------------------------------------------------------------------
#  FastAPI app
# ---------------------------------------------------------------------------

app = FastAPI(
    title="EventHub AI — Attendance Predictor",
    description=(
        "A GradientBoosting-based microservice that predicts actual "
        "attendance for upcoming events with prediction intervals, "
        "and supports continuous learning through a retrain endpoint."
    ),
    version="2.0.0",
)

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
_model_lower = None
_model_upper = None
_columns = None
_lock = threading.Lock()


def _load_model() -> None:
    """Load the trained models & column schema from disk into memory."""
    global _model, _model_lower, _model_upper, _columns
    if not os.path.exists(POINT_MODEL_PATH) or not os.path.exists(COLUMNS_PATH):
        raise FileNotFoundError(
            "Trained model not found. Run `python train.py` first."
        )
    _model = joblib.load(POINT_MODEL_PATH)
    _columns = joblib.load(COLUMNS_PATH)

    # Load quantile models for prediction intervals (optional)
    if os.path.exists(LOWER_MODEL_PATH):
        _model_lower = joblib.load(LOWER_MODEL_PATH)
    if os.path.exists(UPPER_MODEL_PATH):
        _model_upper = joblib.load(UPPER_MODEL_PATH)


@app.on_event("startup")
def startup_event() -> None:
    """Load models into memory when the server starts."""
    try:
        _load_model()
        print("[INFO] All models loaded into memory. [OK]")
    except FileNotFoundError as exc:
        print(f"[WARNING] {exc}")


# ---------------------------------------------------------------------------
#  Pydantic schemas
# ---------------------------------------------------------------------------

EventType = Literal[
    "Exhibition", "Workshop", "Entertainment", "Conference",
    "Seminar", "Meeting", "Festival", "Course"
]

TimePeriod = Literal["Morning", "Evening"]


class PredictRequest(BaseModel):
    """Schema for the /predict endpoint."""
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


class GenerateDescriptionRequest(BaseModel):
    """Schema for the /generate-description endpoint."""
    title: str = Field(..., min_length=2, max_length=200, description="Event title")
    event_type: Optional[str] = Field(None, description="Event type (optional, for context)")


# ---------------------------------------------------------------------------
#  Helper: prepare features for prediction
# ---------------------------------------------------------------------------

def _prepare_features(data: PredictRequest) -> pd.DataFrame:
    """
    Convert a single prediction request into a one-row DataFrame whose
    columns exactly match the model's training columns.
    Total_Days IS a feature in v2 (GradientBoosting learns non-linear
    relationships between days and attendance natively).
    """
    row = {col: 0.0 for col in _columns}

    # Numerical features
    if "Total_Days" in row:
        row["Total_Days"] = float(data.total_days)
    row["Includes_Weekend"] = float(data.includes_weekend)

    # One-hot encoded: Event_Type
    event_col = f"Event_Type_{data.event_type}"
    if event_col in row:
        row[event_col] = 1.0

    # One-hot encoded: Time_Period
    period_col = f"Time_Period_{data.time_period}"
    if period_col in row:
        row[period_col] = 1.0

    return pd.DataFrame([row])


# ---------------------------------------------------------------------------
#  Helper: retrain all models
# ---------------------------------------------------------------------------

def _retrain() -> dict:
    """Reload CSV, retrain all 3 models, and swap into memory."""
    global _model, _model_lower, _model_upper, _columns

    from sklearn.ensemble import GradientBoostingRegressor

    df = pd.read_csv(DATASET_PATH)
    y = np.log1p(df["Actual_Attendance"])

    cols_to_drop = ["Actual_Attendance"]
    if "Proposed_Capacity" in df.columns:
        cols_to_drop.append("Proposed_Capacity")
    X = df.drop(columns=cols_to_drop)
    X = pd.get_dummies(X, columns=["Event_Type", "Time_Period"], drop_first=True)
    X = X.astype(float)

    gb_params = dict(
        n_estimators=300, max_depth=4, learning_rate=0.08,
        min_samples_leaf=5, subsample=0.85, random_state=42,
    )

    # Train all 3 models
    model = GradientBoostingRegressor(loss='squared_error', **gb_params)
    model.fit(X, y)

    model_lower = GradientBoostingRegressor(loss='quantile', alpha=0.15, **gb_params)
    model_lower.fit(X, y)

    model_upper = GradientBoostingRegressor(loss='quantile', alpha=0.85, **gb_params)
    model_upper.fit(X, y)

    # Persist to disk
    os.makedirs(MODEL_DIR, exist_ok=True)
    joblib.dump(model, POINT_MODEL_PATH)
    joblib.dump(model_lower, LOWER_MODEL_PATH)
    joblib.dump(model_upper, UPPER_MODEL_PATH)
    joblib.dump(list(X.columns), COLUMNS_PATH)

    # Hot-swap in memory
    _model = model
    _model_lower = model_lower
    _model_upper = model_upper
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
        "model_type": "GradientBoosting",
    }


@app.post("/predict", tags=["Prediction"])
def predict_attendance(payload: PredictRequest):
    """
    Predict the actual attendance for an upcoming event.
    Returns point estimate + prediction interval (lower/upper bounds).

    **Example JSON body:**
    ```json
    {
        "Event_Type": "Exhibition",
        "Total_Days": 4,
        "Includes_Weekend": 1,
        "Time_Period": "Morning"
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
        pred_log = _model.predict(features)[0]
        lower_log = _model_lower.predict(features)[0] if _model_lower else pred_log
        upper_log = _model_upper.predict(features)[0] if _model_upper else pred_log

    # Convert from log scale back to original scale
    predicted = max(0, round(np.expm1(pred_log)))
    pred_lower = max(0, round(np.expm1(lower_log)))
    pred_upper = max(0, round(np.expm1(upper_log)))

    # Ensure lower <= predicted <= upper
    pred_lower = min(pred_lower, predicted)
    pred_upper = max(pred_upper, predicted)

    return {
        "status": "success",
        "predicted_attendance": predicted,
        "predicted_lower": pred_lower,
        "predicted_upper": pred_upper,
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
    Appends the row to the CSV and retrains all models.
    """
    new_row = {
        "Event_Type": payload.event_type,
        "Total_Days": payload.total_days,
        "Includes_Weekend": payload.includes_weekend,
        "Time_Period": payload.time_period,
        "Actual_Attendance": payload.actual_attendance,
    }
    new_df = pd.DataFrame([new_row])
    new_df.to_csv(DATASET_PATH, mode="a", header=False, index=False)

    with _lock:
        info = _retrain()

    return {
        "status": "success",
        "message": "New event appended and all models retrained successfully.",
        "dataset_rows": info["rows"],
        "model_features": info["features"],
    }


@app.post("/generate-description", tags=["AI Description"])
def generate_description(payload: GenerateDescriptionRequest):
    """
    Generate an AI-powered event description based on the title.
    Uses OpenRouter API with Qwen 2.5 72B Instruct (Free tier).

    **Example JSON body:**
    ```json
    {
        "title": "معرض التقنية الحديثة",
        "event_type": "معرض"
    }
    ```
    """
    openrouter_key = os.getenv("OPENROUTER_API_KEY", "")
    if not openrouter_key:
        raise HTTPException(
            status_code=503,
            detail="OpenRouter API key not configured. Set OPENROUTER_API_KEY in .env",
        )

    # Build a context-aware prompt
    type_context = ""
    if payload.event_type:
        type_context = f" (نوع الفعالية: {payload.event_type})"

    # Strict instructions to prevent "Tattbeel" / marketing hype and garbage formatting
    system_prompt = (
        "أنت مساعد ذكي لمنصة EventHub. مهمتك كتابة وصف تقني، دقيق، ومهني للفعاليات باللغة العربية الفصحى السليمة. "
        "تجنب تماماً أسلوب المبالغة، والترويج الرخيص، والعبارات التسويقية الرنانة (تجنب تماماً التطبيل والعبارات مثل 'فريدة من نوعها'، 'لا تفوت'، 'أروع'، إلخ). "
        "اكتب النص بأسلوب موضوعي يركز على الفائدة العلمية أو العملية والهدف الفعلي للفعالية. "
        "اكتب الوصف مباشرة في جملتين إلى 4 جمل كحد أقصى. "
        "ممنوع كتابة أي مقدمات أو عناوين، وممنوع استخدام الرموز التعبيرية (emojis) أو علامات الاقتباس."
    )

    user_prompt = (
        f"اكتب وصفاً مهنياً وموضوعياً ومباشراً لفعالية بعنوان: \"{payload.title}\"{type_context}. "
        f"ركز على توضيح المحتوى التقني أو الفعلي للفعالية والفئة المستهدفة فقط وبصياغة فصحى راقية ومباشرة."
    )

    try:
        # Request headers for OpenRouter
        headers = {
            "Authorization": f"Bearer {openrouter_key}",
            "Content-Type": "application/json",
            "HTTP-Referer": "https://eventhub.ly",  # Optional, but nice for OpenRouter analytics
            "X-Title": "EventHub AI",
        }

        # Sequential list of free models to try for fallback robustness
        # Each model gets multiple retry attempts with increasing delays
        models_to_try = [
            "google/gemma-4-31b-it:free",
            "google/gemma-4-26b-a4b-it:free",
            "meta-llama/llama-3.3-70b-instruct:free",
        ]

        MAX_RETRIES_PER_MODEL = 3  # Retry each model up to 3 times on rate limit
        last_error = None
        description = None

        import time

        for model in models_to_try:
            for attempt in range(MAX_RETRIES_PER_MODEL):
                try:
                    data = {
                        "model": model,
                        "messages": [
                            {"role": "system", "content": system_prompt},
                            {"role": "user", "content": user_prompt},
                        ],
                        "temperature": 0.3,
                        "max_tokens": 300,
                    }

                    response = httpx.post(
                        "https://openrouter.ai/api/v1/chat/completions",
                        headers=headers,
                        json=data,
                        timeout=25.0,
                    )

                    if response.status_code == 200:
                        resp_json = response.json()
                        choices = resp_json.get("choices")
                        if choices and len(choices) > 0:
                            message = choices[0].get("message")
                            if message and message.get("content"):
                                description = message["content"].strip()
                                break  # Success!
                        else:
                            last_error = f"Model {model} returned empty choices"
                            break  # No point retrying empty choices
                    elif response.status_code == 429:
                        # Rate limited — wait then retry same model
                        wait_seconds = (attempt + 1) * 2  # 2s, 4s, 6s
                        last_error = f"Model {model} rate limited (429), attempt {attempt + 1}"
                        if attempt < MAX_RETRIES_PER_MODEL - 1:
                            time.sleep(wait_seconds)
                        # If last attempt, fall through to next model
                    else:
                        last_error = f"Model {model} returned status {response.status_code}: {response.text}"
                        break  # Non-rate-limit error, try next model
                except Exception as e:
                    last_error = f"Model {model} failed: {str(e)}"
                    break  # Connection error, try next model

            if description:
                break  # Got a successful response, stop trying models

        if not description:
            raise HTTPException(
                status_code=429,
                detail=f"Rate limit reached. Please wait a minute and try again.",
            )

        # Clean up any unwanted prefixes or quotes the model might still return
        for prefix in ["الوصف:", "وصف:", "وصف الفعالية:", "الوصف المهني:"]:
            if description.startswith(prefix):
                description = description[len(prefix):].strip()
        # Remove surrounding quotes if present
        if description.startswith('"') and description.endswith('"'):
            description = description[1:-1].strip()
        if description.startswith('«') and description.endswith('»'):
            description = description[1:-1].strip()

        return {
            "status": "success",
            "description": description,
            "title": payload.title,
        }

    except HTTPException:
        raise
    except httpx.RequestError as exc:
        raise HTTPException(
            status_code=502,
            detail=f"HTTP communication error with OpenRouter: {str(exc)}",
        )
    except Exception as e:
        error_msg = str(e)
        if "429" in error_msg or "rate_limit" in error_msg.lower():
            raise HTTPException(
                status_code=429,
                detail="Rate limit exceeded on OpenRouter. Please wait a moment and try again.",
            )
        raise HTTPException(
            status_code=502,
            detail=f"Failed to generate description: {error_msg}",
        )
