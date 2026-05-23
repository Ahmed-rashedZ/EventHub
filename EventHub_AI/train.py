"""
==========================================================================
 EventHub AI — Model Training Script (v2 — Gradient Boosting)
==========================================================================
 Uses GradientBoostingRegressor for better non-linear pattern capture.
 Trains 3 models:
   1. Point estimate model    (squared_error loss)
   2. Lower-bound model       (quantile α=0.15 → 15th percentile)
   3. Upper-bound model       (quantile α=0.85 → 85th percentile)

 Usage   : python train.py
 Output  : model/ directory containing
              - gb_model.joblib       (point estimate)
              - gb_lower.joblib       (lower bound — 15th percentile)
              - gb_upper.joblib       (upper bound — 85th percentile)
              - model_columns.joblib  (feature column order)
==========================================================================
"""

import os
import sys
import numpy as np
import pandas as pd
from sklearn.ensemble import GradientBoostingRegressor
from sklearn.model_selection import train_test_split
from sklearn.metrics import mean_absolute_error, r2_score, mean_squared_error
import joblib


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

# Old model to clean up
OLD_MODEL_PATH = os.path.join(MODEL_DIR, "linear_regression_model.joblib")


# ---------------------------------------------------------------------------
#  Preprocessing helper
# ---------------------------------------------------------------------------

def preprocess(df: pd.DataFrame) -> tuple[pd.DataFrame, pd.Series]:
    """
    Separate features / target and apply One-Hot Encoding.

    Target  = log1p(Actual_Attendance)
    Features = Total_Days + Includes_Weekend + Event_Type (OHE) + Time_Period (OHE)

    Unlike v1, Total_Days IS a feature — GradientBoosting can learn
    non-linear relationships between days and attendance natively.
    """
    y = np.log1p(df["Actual_Attendance"].copy())

    cols_to_drop = ["Actual_Attendance"]
    if "Proposed_Capacity" in df.columns:
        cols_to_drop.append("Proposed_Capacity")
    X = df.drop(columns=cols_to_drop)

    X = pd.get_dummies(X, columns=["Event_Type", "Time_Period"], drop_first=True)
    X = X.astype(float)

    return X, y


# ---------------------------------------------------------------------------
#  GradientBoosting hyperparameters
# ---------------------------------------------------------------------------

GB_PARAMS = dict(
    n_estimators=300,
    max_depth=4,
    learning_rate=0.08,
    min_samples_leaf=5,
    subsample=0.85,
    random_state=42,
)


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
    columns = list(X.columns)
    print(f"[INFO] Features after encoding: {columns}")

    # 3. Train / Test split (80/20) for evaluation ----------------------------
    X_train, X_test, y_train, y_test = train_test_split(
        X, y, test_size=0.2, random_state=42
    )
    print(f"[INFO] Train: {len(X_train)} rows  |  Test: {len(X_test)} rows")

    # 4. Train Point Estimate Model -------------------------------------------
    model = GradientBoostingRegressor(loss='squared_error', **GB_PARAMS)
    model.fit(X_train, y_train)
    print("[INFO] ✅ Point estimate model trained.")

    # 5. Train Quantile Models for Prediction Intervals -----------------------
    model_lower = GradientBoostingRegressor(loss='quantile', alpha=0.15, **GB_PARAMS)
    model_lower.fit(X_train, y_train)
    print("[INFO] ✅ Lower-bound model (15th percentile) trained.")

    model_upper = GradientBoostingRegressor(loss='quantile', alpha=0.85, **GB_PARAMS)
    model_upper.fit(X_train, y_train)
    print("[INFO] ✅ Upper-bound model (85th percentile) trained.")

    # 6. Evaluate on test set -------------------------------------------------
    y_pred_log = model.predict(X_test)
    y_pred = np.expm1(y_pred_log)
    y_actual = np.expm1(y_test)

    mae = mean_absolute_error(y_actual, y_pred)
    rmse = np.sqrt(mean_squared_error(y_actual, y_pred))
    r2 = r2_score(y_actual, y_pred)

    mask = y_actual > 0
    mape = np.mean(np.abs((y_actual[mask] - y_pred[mask]) / y_actual[mask])) * 100
    within_30 = np.mean(np.abs((y_actual[mask] - y_pred[mask]) / y_actual[mask]) <= 0.30) * 100

    print(f"\n{'='*60}")
    print(f"  📊  Test Set Evaluation (20% holdout)")
    print(f"{'='*60}")
    print(f"  MAE  (Mean Absolute Error):     {mae:,.0f}")
    print(f"  RMSE (Root Mean Squared Error):  {rmse:,.0f}")
    print(f"  MAPE (Mean Abs Percentage Error): {mape:.1f}%")
    print(f"  R²   (Coefficient of Determination): {r2:.4f}")
    print(f"  Accuracy@30% (within 30%):       {within_30:.1f}%")
    print(f"{'='*60}")

    # 7. Retrain on FULL dataset for production use ---------------------------
    model.fit(X, y)
    model_lower.fit(X, y)
    model_upper.fit(X, y)
    print("\n[INFO] All 3 models retrained on full dataset for production.")

    # 8. Save models & column schema ------------------------------------------
    os.makedirs(MODEL_DIR, exist_ok=True)
    joblib.dump(model, POINT_MODEL_PATH)
    joblib.dump(model_lower, LOWER_MODEL_PATH)
    joblib.dump(model_upper, UPPER_MODEL_PATH)
    joblib.dump(columns, COLUMNS_PATH)

    # Clean up old linear regression model
    if os.path.exists(OLD_MODEL_PATH):
        os.remove(OLD_MODEL_PATH)
        print("[INFO] 🗑️  Removed old linear regression model.")

    print(f"[INFO] Models saved  →  {MODEL_DIR}/")
    print("[INFO] Training complete. ✅")


# ---------------------------------------------------------------------------
#  Entry-point
# ---------------------------------------------------------------------------

if __name__ == "__main__":
    train_model()
