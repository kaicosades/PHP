# import requests
# from bs4 import BeautifulSoup
# import re
# import openpyxl
# import time

# HEADERS = {
#     "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64)"
# }

# wb = openpyxl.Workbook()
# ws = wb.active
# ws.append(["Название болезни", "Ссылка", "Симптомы"])

# def has_dash_code(text):
#     return bool(re.search(r"\b\w+-\w+\b", text))

# def parse_symptoms_page(url):
#     r = requests.get(url, headers=HEADERS)
#     soup = BeautifulSoup(r.content, "html.parser")

#     # Ищем заголовки с симптомами (h2 или h3 с "симптом")
#     header = soup.find(lambda tag: tag.name in ["h2", "h3"] and "симптом" in tag.text.lower())
#     if header:
#         symptoms = []
#         for sibling in header.find_next_siblings():
#             if sibling.name in ["h2", "h3"]:
#                 break
#             symptoms.append(sibling.get_text(strip=True))
#         return "\n".join(symptoms).strip()

#     # fallback — пытаемся найти блок с классом, содержащим "symptoms"
#     possible = soup.find(class_=re.compile("symptom", re.I))
#     if possible:
#         return possible.get_text(strip=True)

#     return ""

# def parse_page(url):
#     print(f"Парсим: {url}")
#     r = requests.get(url, headers=HEADERS)
#     soup = BeautifulSoup(r.content, "html.parser")
#     links = soup.select("a.b-tree-detail__subcatlist-link")

#     if not links:
#         # Нет подболезней — пытаемся взять симптомы со страницы болезни
#         symptoms = parse_symptoms_page(url)
#         return [("", url, symptoms)]

#     results = []
#     for link in links:
#         title = link.get_text(strip=True)
#         href = link.get("href")
#         if not href.startswith("http"):
#             href = "https://www.rlsnet.ru" + href
#         # Рекурсия всегда — заходить внутрь, если есть подкатегории
#         subresults = parse_page(href)
#         if has_dash_code(title):
#             # Группа — просто добавляем результаты из подстраницы
#             results.extend(subresults)
#         else:
#             # Конечная болезнь — добавляем саму и подболезни, если есть
#             symptoms = parse_symptoms_page(href)
#             results.append((title, href, symptoms))
#             results.extend(subresults)
#         time.sleep(0.5)
#     return results

# category_urls = [
#     "https://www.rlsnet.ru/mkb/klass-i-nekotorye-infekcionnye-i-parazitarnye-bolezni-4",
#     # остальные ссылки
# ]

# all_results = []
# for url in category_urls:
#     all_results.extend(parse_page(url))

# for title, href, symptoms in all_results:
#     if title:
#         ws.append([title, href, symptoms])

# wb.save("болезни_с_симптомами_глубоко.xlsx")
# print("Готово.")

import requests
from bs4 import BeautifulSoup
import re
import openpyxl
import time

HEADERS = {
    "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64)"
}

wb = openpyxl.Workbook()
ws = wb.active
ws.append(["Название болезни", "Ссылка", "Симптомы / Описание"])

results = []  # ← глобальный список для накопления

def has_dash_code(text):
    return bool(re.search(r"\b\w+-\w+\b", text))

def parse_symptoms_page(url):
    r = requests.get(url, headers=HEADERS)
    soup = BeautifulSoup(r.content, "html.parser")

    description_block = soup.select_one("#description")
    if description_block:
        return description_block.get_text(strip=True)

    header = soup.find(lambda tag: tag.name in ["h2", "h3"] and "симптом" in tag.text.lower())
    if header:
        symptoms = []
        for sibling in header.find_next_siblings():
            if sibling.name in ["h2", "h3"]:
                break
            symptoms.append(sibling.get_text(strip=True))
        return "\n".join(symptoms).strip()

    return ""

def parse_page(url):
    print(f"Парсим: {url}")
    r = requests.get(url, headers=HEADERS)
    soup = BeautifulSoup(r.content, "html.parser")
    links = soup.select("a.b-tree-detail__subcatlist-link")

    if not links:
        symptoms = parse_symptoms_page(url)
        results.append(("", url, symptoms))
        return

    for link in links:
        title = link.get_text(strip=True)
        href = link.get("href")
        if not href.startswith("http"):
            href = "https://www.rlsnet.ru" + href
        print(f"  ➤ {title}")
        if has_dash_code(title):
            parse_page(href)
        else:
            symptoms = parse_symptoms_page(href)
            results.append((title, href, symptoms))
            parse_page(href)
        time.sleep(0.5)

category_urls = [
    "https://www.rlsnet.ru/mkb/klass-i-nekotorye-infekcionnye-i-parazitarnye-bolezni-4",
    "https://www.rlsnet.ru/mkb/klass-ii-novoobrazovaniya-454",
    "https://www.rlsnet.ru/mkb/klass-iii-bolezni-krovi-krovetvornyx-organov-i-otdelnye-naruseniya-vovlekayushhie-immunnyi-mexanizm-112",
    "https://www.rlsnet.ru/mkb/klass-iv-bolezni-endokrinnoi-sistemy-rasstroistva-pitaniya-i-naruseniya-obmena-veshhestv-198",
    "https://www.rlsnet.ru/mkb/klass-v-psixiceskie-rasstroistva-i-rasstroistva-povedeniya-81",
    "https://www.rlsnet.ru/mkb/klass-vi-bolezni-nervnoi-sistemy-141",
    "https://www.rlsnet.ru/mkb/klass-vii-bolezni-glaza-i-ego-pridatocnogo-apparata-354",
    "https://www.rlsnet.ru/mkb/klass-viii-bolezni-uxa-i-soscevidnogo-otrostka-155",
    "https://www.rlsnet.ru/mkb/klass-ix-bolezni-sistemy-krovoobrashheniya-58",
    "https://www.rlsnet.ru/mkb/klass-x-bolezni-organov-dyxaniya-16",
    "https://www.rlsnet.ru/mkb/klass-xi-bolezni-organov-pishhevareniya-24",
    "https://www.rlsnet.ru/mkb/klass-xii-bolezni-kozi-i-podkoznoi-kletcatki-26",
    "https://www.rlsnet.ru/mkb/klass-xiii-bolezni-kostno-mysecnoi-sistemy-i-soedinitelnoi-tkani-31",
    "https://www.rlsnet.ru/mkb/klass-xiv-bolezni-mocepolovoi-sistemy-46",
    "https://www.rlsnet.ru/mkb/klass-xv-beremennost-rody-i-poslerodovoi-period-201",
    "https://www.rlsnet.ru/mkb/klass-xvi-otdelnye-sostoyaniya-voznikayushhie-v-perinatalnom-periode-162",
    "https://www.rlsnet.ru/mkb/klass-xvii-vrozdennye-anomalii-poroki-razvitiya-deformacii-i-xromosomnye-naruseniya-1472",
    "https://www.rlsnet.ru/mkb/klass-xviii-simptomy-priznaki-i-otkloneniya-ot-normy-vyyavlennye-pri-kliniceskix-i-laboratornyx-issledovaniyax-ne-klassificirovannye-v-drugix-rubrikax-75",
    "https://www.rlsnet.ru/mkb/klass-xix-travmy-otravleniya-i-nekotorye-drugie-posledstviya-vozdeistviya-vnesnix-pricin-55",
    "https://www.rlsnet.ru/mkb/klass-xx-vremennye-oboznaceniya-novyx-diagnozov-neyasnoi-etiologii-ili-dlya-ispolzovaniya-v-cs-11389",
    "https://www.rlsnet.ru/mkb/klass-xx-vnesnie-priciny-zabolevaemosti-i-smertnosti-349",
    "https://www.rlsnet.ru/mkb/klass-xxi-faktory-vliyayushhie-na-sostoyanie-zdorovya-i-obrashheniya-v-ucrezdeniya-zdravooxraneniya-296",
    "https://www.rlsnet.ru/mkb/klass-xxii-xirurgiceskaya-praktika-297"
]

try:
    for url in category_urls:
        parse_page(url)
except KeyboardInterrupt:
    print("\nПрервано вручную. Записываю собранные данные...")

for title, href, symptoms in results:
    if title:
        ws.append([title, href, symptoms])

wb.save("болезни_с_описанием.xlsx")
print("Готово.")



