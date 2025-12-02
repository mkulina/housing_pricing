"""
Model Training Module
Handles loading data and training the housing price prediction model.
"""
import os
import sys
import pickle
import pandas as pd
from sklearn.linear_model import LinearRegression

# Add parent directory to path for config import
sys.path.insert(0, os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
from config.settings import DATA_PATH, MODEL_PATH, FEATURES, TARGET


def load_training_data(data_path: str) -> pd.DataFrame:
    """Load training data from CSV file."""
    if not os.path.exists(data_path):
        raise FileNotFoundError(f"Training data not found at {data_path}")
    return pd.read_csv(data_path)


def train_model(X, y) -> LinearRegression:
    """Train a linear regression model."""
    model = LinearRegression()
    model.fit(X, y)
    return model


def save_model(model, model_path: str) -> None:
    """Save trained model to disk."""
    os.makedirs(os.path.dirname(model_path), exist_ok=True)
    with open(model_path, 'wb') as f:
        pickle.dump(model, f)


def main():
    """Main training pipeline."""
    print("Loading training data...")
    df = load_training_data(DATA_PATH)
    
    X = df[FEATURES].values
    y = df[TARGET].values
    
    print(f"Training model with {len(df)} samples...")
    model = train_model(X, y)
    
    print("Saving model...")
    save_model(model, MODEL_PATH)
    
    print("\nTraining complete!")
    print(f"Model saved to: {MODEL_PATH}")
    print(f"Coefficients: {model.coef_}")
    print(f"Intercept: {model.intercept_}")
    
    return model


if __name__ == '__main__':
    main()

