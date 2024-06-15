import time
import sys
import json
import logging
import config
import traceback
import os
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC

log_dir = os.path.dirname(os.path.abspath(__file__))
log_file = os.path.join(log_dir, 'log/selenium_test.log')
logging.basicConfig(filename=log_file, level=logging.INFO)

try:
    logging.info("Menjalankan Program Selenium Test")
    options = webdriver.ChromeOptions()
    options.add_argument(f"--user-data-dir={config.user_data_dir}")
    options.add_argument(f"--profile-directory={config.profile_dir}")
    options.add_argument("--disable-blink-features=AutomationControlled")
    options.add_argument("--disable-infobars")
    options.add_experimental_option("excludeSwitches", ["enable-automation"])
    options.add_experimental_option('useAutomationExtension', False)


    driver = webdriver.Chrome(options=options)
    driver.get("https://www.google.com/")

    time.sleep(20)

    driver.quit()

except KeyboardInterrupt:
    logging.info("Program interrupted by user")
    try:
        driver.quit()
    except:
        pass
    sys.exit(0)

except Exception as e:
    tb = traceback.format_exc()
    error_message = f"Terjadi kesalahan: {e}\n{tb}"
    logging.info(error_message)
    sys.stderr.write(json.dumps({"status": "error", "message": error_message}))
    try:
        driver.quit()
    except:
        pass
    sys.exit(1)
