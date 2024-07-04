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
from datetime import datetime, timedelta
from selenium.webdriver.common.action_chains import ActionChains


task_data = json.loads(sys.argv[1])

log_dir = os.path.dirname(os.path.abspath(__file__))
log_file = os.path.join(log_dir, 'log/shopee_seller_center_live_streaming.log')
logging.basicConfig(filename=log_file, level=logging.INFO)

# Memecah tanggal
scheduled_to_run = datetime.strptime(task_data['scheduled_to_run'], "%Y-%m-%d %H:%M:%S")
year = scheduled_to_run.year
month = scheduled_to_run.strftime('%b')  # Mengambil nama bulan dalam format tiga huruf
day = scheduled_to_run.day

logging.info(f"ID : {task_data['id']} , Type : {task_data['type']} , Link : {task_data['link']} , Untuk Data Tanggal =  Year: {year}, Month: {month}, Day: {day}")

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

    wait.until(EC.presence_of_all_elements_located((By.XPATH, "//button//span[text()=' Rincian ']")))

    # Find all buttons with text "Rincian"
    rincians = driver.find_elements(By.XPATH, "//button//span[text()=' Rincian ']")

    # Get the main window handle
    main_window = driver.current_window_handle

    # Iterate and click each button using JavaScript
    for rincian in rincians:
        # Use JavaScript to click the button
        driver.execute_script("arguments[0].click();", rincian)

        # Wait for the new window or tab to open
        wait.until(EC.number_of_windows_to_be(2))

        # Get all window handles
        all_windows = driver.window_handles

        # Switch to the new window
        for window in all_windows:
            if window != main_window:
                driver.switch_to.window(window)
                break

        # Wait for the download button to be present in the new window
        download_button = wait.until(
            EC.presence_of_element_located((By.XPATH, "//*[contains(text(), 'Download Data')]"))
        )

        # Click the download button using JavaScript
        driver.execute_script("arguments[0].click();", download_button)
        time.sleep(5)

        # Optionally, wait for the download to complete or handle the new page actions
        # For example, you might wait for a specific element to appear indicating the download is complete
        # wait.until(EC.presence_of_element_located((By.XPATH, "//*[contains(text(), 'Download Complete')]")))

        # Close the new window and switch back to the main window
        driver.close()
        driver.switch_to.window(main_window)
    

    time.sleep(20)
    driver.quit()

    # pilih_periode = wait.until(EC.presence_of_element_located((By.XPATH, "//*[contains(text(), 'Periode Data')]")))
    # driver.execute_script("arguments[0].click();", pilih_periode)
    # time.sleep(7)

    # pilih_periode_30_days = wait.until(EC.presence_of_element_located((By.XPATH, "//*[contains(text(), '30 hari sebelumnya')]")))
    # driver.execute_script("arguments[0].click();", pilih_periode_30_days)
    # time.sleep(7)

    # button = wait.until(EC.presence_of_element_located((By.XPATH, "//*[contains(text(), 'Download Data')]")))
    # download_timestamp = time.time()
    # driver.execute_script("arguments[0].click();", button)

    # # Wait for the file to be downloaded
    # downloaded_file = helper.check_file_downloaded(config.download_directory, download_timestamp)
    # driver.quit()

    # if not downloaded_file:
    #     raise FileNotFoundError("File tidak berhasil diunduh dalam batas waktu yang ditentukan.")
    
    # worksheet_name = "Daftar Streaming"
    # df = pd.read_excel(downloaded_file, sheet_name=worksheet_name, header=0)

    # data_json = df.to_dict(orient='records')
    # cleaned_data_json = helper.clean_data(data_json)

    # output = {
    #     "status": "success",
    #     "data": cleaned_data_json,
    #     "file_name": os.path.basename(downloaded_file)
    # }
    # print(json.dumps(output))    

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