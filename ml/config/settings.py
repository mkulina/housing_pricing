"""
ML Model Configuration Settings
"""
import os

# Base directory
BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))

# Paths
DATA_PATH = os.path.join(BASE_DIR, 'data', 'training_data.csv')
MODEL_PATH = os.path.join(BASE_DIR, 'models', 'housing_model.pkl')

# Model settings
MODEL_TYPE = 'linear_regression'
FEATURES = ['square_footage', 'bedrooms']
TARGET = 'price'

# Validation
MIN_SQUARE_FOOTAGE = 100
MAX_SQUARE_FOOTAGE = 10000
MIN_BEDROOMS = 1
MAX_BEDROOMS = 10

