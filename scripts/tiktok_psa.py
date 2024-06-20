import time
import pandas as pd
import os
import helper
import config
import sys
import json
import logging
import traceback
from datetime import datetime, timedelta
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC

task_data = json.loads(sys.argv[1])

log_dir = os.path.dirname(os.path.abspath(__file__))
log_file = os.path.join(log_dir, 'log/tiktok_psa.log')
logging.basicConfig(filename=log_file, level=logging.INFO)

try:
    options = webdriver.ChromeOptions()
    options.add_argument(f"--user-data-dir={config.user_data_dir}")
    options.add_argument(f"--profile-directory={config.profile_dir}")
    options.add_argument("--disable-blink-features=AutomationControlled")
    options.add_argument("--disable-infobars")
    options.add_experimental_option("excludeSwitches", ["enable-automation"])
    options.add_experimental_option('useAutomationExtension', False)
    
    driver = webdriver.Chrome(options=options)
    url = task_data['link']  # Menggunakan link dari task data
    driver.get(url)
    wait = WebDriverWait(driver, 30)

    schedule_to_run = task_data['scheduled_to_run']
    schedule_date = datetime.strptime(schedule_to_run, '%Y-%m-%d %H:%M:%S')
    current_date = datetime.now()

    if current_date >= schedule_date + timedelta(days=1):

        logging.info("Tanggal sudah terlewat minimal satu hari, melakukan export data 30 hari terakhir")

        click_select_timeframe = wait.until(EC.element_to_be_clickable((By.XPATH, "(//span[contains(.,'Last 7 days')])[2]")))
        time.sleep(7)
        click_select_timeframe.click()
        time.sleep(7)

        select_timeframe = wait.until(EC.element_to_be_clickable((By.XPATH, "//*[contains(text(), 'Last 30 days')]")))
        time.sleep(7)
        select_timeframe.click()
        time.sleep(7)

    first_export = wait.until(EC.element_to_be_clickable((By.XPATH, "//*[contains(text(), 'Export')]")))
    time.sleep(7)
    first_export.click()
    time.sleep(7)

    second_export = wait.until(EC.element_to_be_clickable((By.XPATH, "(//span[contains(.,'Export')])[5]")))
    download_timestamp = time.time()
    second_export.click()

    downloaded_file = helper.check_file_downloaded(config.download_directory, download_timestamp)
    driver.quit()

    if not downloaded_file:
        raise FileNotFoundError("File tidak berhasil diunduh dalam batas waktu yang ditentukan.")
    
    worksheet_name = "Sheet1"
    df = pd.read_excel(downloaded_file, sheet_name=worksheet_name, header=0)

    # Memfilter data untuk menghilangkan baris yang kolom Date mengandung kata "Total"
    filtered_df = df[~df['Date'].str.contains('Total', na=False)]

    data_json = filtered_df.to_dict(orient='records')
    cleaned_data_json = helper.clean_data(data_json)

    output = {
        "status": "success",
        "data": cleaned_data_json,
        "file_name": os.path.basename(downloaded_file)
    }
    print(json.dumps(output))

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
