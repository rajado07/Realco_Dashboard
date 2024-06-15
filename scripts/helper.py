# helper.py
import os
import glob
import time
import math
from datetime import datetime, date
import logging
from logging.handlers import TimedRotatingFileHandler


def check_file_downloaded(directory, download_timestamp):
    timeout = 30  # timeout in seconds
    elapsed_time = 0
    sleep_interval = 1  # interval to check for new files
    while elapsed_time < timeout:
        current_files = glob.glob(os.path.join(directory, '*.xlsx'))
        for file_path in current_files:
            if os.path.getmtime(file_path) > download_timestamp:
                return file_path
        time.sleep(sleep_interval)
        elapsed_time += sleep_interval
    return None

def clean_data(data):
    """Clean the data by replacing non-compliant float values with None, converting datetime and date to string."""
    if isinstance(data, list):
        return [clean_data(item) for item in data]
    elif isinstance(data, dict):
        return {key: clean_data(value) for key, value in data.items()}
    elif isinstance(data, float):
        if math.isnan(data) or math.isinf(data):
            return None  # Replace non-compliant values with None
        return data
    elif isinstance(data, (datetime, date)):
        return data.isoformat()  # Convert datetime and date to string
    return data

def configure_logging(log_name):
    log_dir = os.path.dirname(os.path.abspath(__file__))
    log_path = os.path.join(log_dir, 'log')
    
    if not os.path.exists(log_path):
        os.makedirs(log_path)

    log_file = os.path.join(log_path, f'{log_name}.log')

    logger = logging.getLogger(log_name)

    # Configure TimedRotatingFileHandler
    handler = TimedRotatingFileHandler(log_file, when="midnight", interval=1, backupCount=30)
    handler.setLevel(logging.DEBUG)
    formatter = logging.Formatter('%(asctime)s - %(levelname)s - %(message)s')
    handler.setFormatter(formatter)
    logger.addHandler(handler)

    return logger