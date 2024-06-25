import time
import pandas as pd
import os
import helper
import config
import sys
import json
import logging
import traceback
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from datetime import datetime

task_data = json.loads(sys.argv[1])

log_dir = os.path.dirname(os.path.abspath(__file__))
log_file = os.path.join(log_dir, 'log/shopee_brand_portal_shop.log')
logging.basicConfig(filename=log_file, level=logging.INFO)

# Memecah tanggal
scheduled_to_run = datetime.strptime(task_data['scheduled_to_run'], "%Y-%m-%d %H:%M:%S")
year = scheduled_to_run.year
month = scheduled_to_run.strftime('%b')  # Mengambil nama bulan dalam format tiga huruf
day = scheduled_to_run.day

logging.info(f"ID : {task_data['id']} , Type : {task_data['type']} ,  Link : {task_data['link']} ,  Untuk Data Tanggal =  Year: {year}, Month: {month}, Day: {day}")

try:
    options = webdriver.ChromeOptions()
    options.add_argument(f"--user-data-dir={config.user_data_dir}")
    options.add_argument(f"--profile-directory={config.profile_dir}")
    options.add_argument("--disable-blink-features=AutomationControlled")
    options.add_argument("--disable-infobars")
    options.add_experimental_option("excludeSwitches", ["enable-automation"])
    options.add_experimental_option('useAutomationExtension', False)
    
    driver = webdriver.Chrome(options=options)
    url = task_data['link']
    driver.get(url)
    wait = WebDriverWait(driver, 30)

    pilih_periode = wait.until(EC.presence_of_element_located((By.XPATH, "//*[contains(text(), 'Last 7 days')]")))
    driver.execute_script("arguments[0].click();", pilih_periode)
    time.sleep(3)

    pilih_by_day = wait.until(EC.presence_of_element_located((By.XPATH, "//*[contains(text(), 'By Day')]")))
    pilih_by_day.click()
    time.sleep(3)

    select_year = wait.until(EC.presence_of_element_located((By.XPATH, "//span[@class='date-default-style year']")))
    select_year.click()
    time.sleep(3)

    pick_year = wait.until(EC.presence_of_element_located((By.XPATH, f"//div[contains(@class, 'shopee-react-date-picker__table-cell') and text()='{year}']")))
    pick_year.click()
    time.sleep(3)

    select_month = wait.until(EC.presence_of_element_located((By.XPATH, "//span[@class='date-default-style month']")))
    select_month.click()
    time.sleep(3)

    pick_month = wait.until(EC.presence_of_element_located((By.XPATH, f"//div[contains(@class, 'shopee-react-date-picker__table-cell') and text()='{month}']")))
    pick_month.click()
    time.sleep(3)

    pick_day = wait.until(EC.presence_of_element_located((By.XPATH, f"//div[contains(@class, 'shopee-react-date-picker__table-cell') and not(contains(@class, 'out-of-range')) and text()='{day}']")))
    pick_day.click()
    time.sleep(3)

    confirm_button = wait.until(EC.presence_of_element_located((By.XPATH, "//button[.//span[text()='Confirm']]")))
    confirm_button.click()
    time.sleep(3)

    refresh = wait.until(EC.presence_of_element_located((By.XPATH, "//*[contains(text(), 'Refresh')]")))
    driver.execute_script("arguments[0].click();", refresh)
    time.sleep(3) 

    apply = wait.until(EC.presence_of_element_located((By.XPATH, "//*[contains(text(), 'Apply')]")))
    driver.execute_script("arguments[0].click();", apply)
    time.sleep(3)

    button = wait.until(EC.presence_of_element_located((By.XPATH, "//button[contains(@data-track-info, '\"targetType\":\"export\"')]")))
    download_timestamp = time.time()
    driver.execute_script("arguments[0].click();", button)

    # Wait for the file to be downloaded
    downloaded_file = helper.check_file_downloaded(config.download_directory, download_timestamp)
    driver.quit()

    if not downloaded_file:
        raise FileNotFoundError("File tidak berhasil diunduh dalam batas waktu yang ditentukan.")
    
    worksheet_name = "Product Ranking"
    df = pd.read_excel(downloaded_file, sheet_name=worksheet_name, header=1)

    data_json = df.to_dict(orient='records')
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
    logging.error(error_message)
    sys.stderr.write(json.dumps({"status": "error", "message": error_message}))
    try:
        driver.quit()
    except:
        pass
    sys.exit(1)
