import time
import os
import json
import logging
import traceback
import helper
import config
import sys
from datetime import datetime
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.common.action_chains import ActionChains

# Data yang diterima dari argumen skrip
task_data = json.loads(sys.argv[1])

# Pengaturan logging
log_dir = os.path.dirname(os.path.abspath(__file__))
log_file = os.path.join(log_dir, 'log/shopee_seller_center_live_streaming.log')
logging.basicConfig(filename=log_file, level=logging.INFO)

# Memecah tanggal
scheduled_to_run = datetime.strptime(task_data['scheduled_to_run'], "%Y-%m-%d %H:%M:%S")
year = scheduled_to_run.year
month = scheduled_to_run.strftime('%b')  # Mengambil nama bulan dalam format tiga huruf
day = scheduled_to_run.day

logging.info(f"ID : {task_data['id']} , Type : {task_data['type']} , Link : {task_data['link']} , Untuk Data Tanggal =  Year: {year}, Month: {month}, Day: {day}")

def get_table_data(wait):
    table = wait.until(EC.presence_of_element_located((By.TAG_NAME, 'table')))
    thead = wait.until(EC.presence_of_element_located((By.TAG_NAME, 'thead')))
    tbody = wait.until(EC.presence_of_element_located((By.TAG_NAME, 'tbody')))

    # Ambil header dari tabel
    headers = thead.find_elements(By.TAG_NAME, 'th')
    header_list = [header.text for header in headers]

    # Ambil data dari tabel
    rows = tbody.find_elements(By.TAG_NAME, 'tr')
    data = []

    for row in rows:
        cols = row.find_elements(By.TAG_NAME, 'td')
        row_data = {header_list[i]: cols[i].text for i in range(len(cols))}
        data.append(row_data)
    
    return data

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

    pilih_periode = wait.until(EC.presence_of_element_located((By.XPATH, "//*[contains(text(), 'Periode Data')]")))
    driver.execute_script("arguments[0].click();", pilih_periode)
    time.sleep(3)

    time.sleep(20)

    all_data = get_table_data(wait)

    # Cek apakah ada elemen pagination
    pagination = driver.find_elements(By.CSS_SELECTOR, 'ul.eds-pager__pages > li')
    valid_pages = [li for li in pagination if li.text.isdigit()]

    if valid_pages:
        last_page_num = int(valid_pages[-1].text)  # Ambil nomor halaman terakhir dari elemen pagination

        for page_num in range(2, last_page_num + 1):
            next_button = wait.until(EC.element_to_be_clickable((By.XPATH, f"//ul[@class='eds-pager__pages']/li[text()='{page_num}']")))
            next_button.click()  # Klik tombol untuk pindah ke halaman berikutnya

            # Tunggu hingga tabel pada halaman baru dimuat
            WebDriverWait(driver, 10).until(
                EC.text_to_be_present_in_element(
                    (By.CSS_SELECTOR, 'ul.eds-pager__pages > li.active'), str(page_num)
                )
            )

            # Ambil data dari halaman berikutnya
            page_data = get_table_data(wait)
            all_data.extend(page_data)

    if not all_data:
        raise Exception("JSON kosong, data tidak tersedia")

    # Simpan data ke file untuk debug
    with open('data.json', 'w', encoding='utf-8') as f:
        json.dump(all_data, f, ensure_ascii=False, indent=4)

    # Konversi data ke format JSON tanpa escape sequence
    json_data = json.dumps(all_data, ensure_ascii=False, indent=4)

    driver.quit()

    output = {
        "status": "success",
        "data": json.loads(json_data),
        "file_name": "Mengambil Lansung Data Table"
    }
    print(json.dumps(output, indent=4))  # Output JSON dengan format yang rapi

except Exception as e:
    tb = traceback.format_exc()
    error_message = f"Terjadi kesalahan: {e}\n{tb}"
    logging.error(error_message)
    sys.stderr.write(json.dumps({"status": "error", "message": error_message}, indent=4))
    try:
        driver.quit()
    except:
        pass
    sys.exit(1)
