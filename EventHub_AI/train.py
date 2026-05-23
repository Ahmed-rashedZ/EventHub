"""
==========================================================================
 EventHub AI Microservice — Model Training Script
==========================================================================
 Purpose : Load the historical events CSV, preprocess categorical features
           with One-Hot Encoding, train a Linear Regression model, and
           persist the model + column schema to disk for the API to load.

 Usage   : python train.py
 Output  : model/  directory containing
              - linear_regression_model.joblib   (trained model)
              - model_columns.joblib             (feature column order)
==========================================================================
"""

import os
import sys
import pandas as pd
from sklearn.linear_model import LinearRegression
from sklearn.model_selection import train_test_split
from sklearn.metrics import mean_absolute_error, r2_score
import joblib


# ---------------------------------------------------------------------------
#  Configuration
# ---------------------------------------------------------------------------

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
DATASET_PATH = os.path.join(BASE_DIR, "synthetic_events_dataset.csv")
MODEL_DIR = os.path.join(BASE_DIR, "model")
MODEL_PATH = os.path.join(MODEL_DIR, "linear_regression_model.joblib")
COLUMNS_PATH = os.path.join(MODEL_DIR, "model_columns.joblib")


# ---------------------------------------------------------------------------
#  Preprocessing helper
# ---------------------------------------------------------------------------

import numpy as np

def preprocess(df: pd.DataFrame) -> tuple[pd.DataFrame, pd.Series]:
    """
    Separate features / target and apply One-Hot Encoding to categorical
    columns (Event_Type, Time_Period).  Returns (X, y).

    NOTE: Proposed_Capacity is excluded from features.
    The model predicts attendance based on event characteristics alone,
    so the manager can use the prediction to DECIDE what capacity to set.
    """
    # Separate target (log-transformed to handle large ranges and keep predictions positive)
    y = np.log1p(df["Actual_Attendance"].copy())
    
    # Drop target and Proposed_Capacity (if it exists)
    cols_to_drop = ["Actual_Attendance"]
    if "Proposed_Capacity" in df.columns:
        cols_to_drop.append("Proposed_Capacity")
    X = df.drop(columns=cols_to_drop)

    # One-Hot Encode categorical features (drop_first avoids multicollinearity)
    X = pd.get_dummies(X, columns=["Event_Type", "Time_Period"], drop_first=True)

    # Ensure all columns are numeric (safety check)
    X = X.astype(float)

    return X, y


# ---------------------------------------------------------------------------
#  Training pipeline
# ---------------------------------------------------------------------------

def train_model() -> None:
    """Full training pipeline: load → preprocess → train → evaluate → save."""

    # 1. Load dataset ---------------------------------------------------------
    if not os.path.exists(DATASET_PATH):
        print(f"[ERROR] Dataset not found at: {DATASET_PATH}")
        sys.exit(1)

    df = pd.read_csv(DATASET_PATH)
    print(f"[INFO] Loaded dataset  →  {len(df)} rows, {len(df.columns)} columns")

    # 2. Preprocess -----------------------------------------------------------
    X, y = preprocess(df)
    print(f"[INFO] Features after encoding: {list(X.columns)}")

    # 3. Train / Test split (80/20) for evaluation ----------------------------
    X_train, X_test, y_train, y_test = train_test_split(
        X, y, test_size=0.2, random_state=42
    )

    # 4. Train Linear Regression model ----------------------------------------
    model = LinearRegression()
    model.fit(X_train, y_train)
    print("[INFO] Model trained successfully.")

    # 5. Evaluate on test set -------------------------------------------------
    y_pred = model.predict(X_test)
    # Convert back from log scale to evaluate on the original scale
    y_test_orig = np.expm1(y_test)
    y_pred_orig = np.expm1(y_pred)
    mae = mean_absolute_error(y_test_orig, y_pred_orig)
    r2 = r2_score(y_test, y_pred)
    print(f"[INFO] Evaluation  →  MAE (original scale): {mae:.2f}  |  R² (log scale): {r2:.4f}")

    # 6. Retrain on FULL dataset for production use ---------------------------
    model.fit(X, y)
    print("[INFO] Model retrained on full dataset for production.")

    # 7. Save model & column schema -------------------------------------------
    os.makedirs(MODEL_DIR, exist_ok=True)
    joblib.dump(model, MODEL_PATH)
    joblib.dump(list(X.columns), COLUMNS_PATH)
    print(f"[INFO] Model saved     →  {MODEL_PATH}")
    print(f"[INFO] Columns saved   →  {COLUMNS_PATH}")
    print("[INFO] Training complete. ✅")


# ---------------------------------------------------------------------------
#  Entry-point
# ---------------------------------------------------------------------------

if __name__ == "__main__":
    train_model()
