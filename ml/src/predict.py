"""
Prediction Module
Handles loading the trained model and making predictions.
"""
import os
import sys
import pickle
import numpy as np

# Add parent directory to path for config import
sys.path.insert(0, os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
from config.settings import MODEL_PATH, MIN_SQUARE_FOOTAGE, MAX_SQUARE_FOOTAGE, MIN_BEDROOMS, MAX_BEDROOMS


class PredictionError(Exception):
    """Custom exception for prediction errors."""
    pass


def load_model(model_path: str):
    """Load trained model from disk."""
    if not os.path.exists(model_path):
        raise PredictionError(f"Model not found at {model_path}. Please run training first.")
    
    with open(model_path, 'rb') as f:
        return pickle.load(f)


def validate_input(square_footage: float, bedrooms: float) -> None:
    """Validate input parameters."""
    if not MIN_SQUARE_FOOTAGE <= square_footage <= MAX_SQUARE_FOOTAGE:
        raise PredictionError(
            f"Square footage must be between {MIN_SQUARE_FOOTAGE} and {MAX_SQUARE_FOOTAGE}"
        )
    
    if not MIN_BEDROOMS <= bedrooms <= MAX_BEDROOMS:
        raise PredictionError(
            f"Bedrooms must be between {MIN_BEDROOMS} and {MAX_BEDROOMS}"
        )


def predict(square_footage: float, bedrooms: float) -> float:
    """Make a price prediction for given inputs."""
    validate_input(square_footage, bedrooms)
    
    model = load_model(MODEL_PATH)
    X = np.array([[square_footage, bedrooms]])
    prediction = model.predict(X)[0]
    
    return max(0, prediction)  # Ensure non-negative price


def main():
    """CLI entry point for predictions."""
    if len(sys.argv) != 3:
        print("Usage: python predict.py <square_footage> <bedrooms>", file=sys.stderr)
        sys.exit(1)
    
    try:
        square_footage = float(sys.argv[1])
        bedrooms = float(sys.argv[2])
        
        result = predict(square_footage, bedrooms)
        print(result)
        
    except PredictionError as e:
        print(f"Error: {e}", file=sys.stderr)
        sys.exit(1)
    except ValueError as e:
        print(f"Invalid input: {e}", file=sys.stderr)
        sys.exit(1)


if __name__ == '__main__':
    main()

