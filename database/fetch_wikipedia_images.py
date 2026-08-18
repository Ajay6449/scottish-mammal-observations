import requests
import json
import time

# List of all 34 species with their key and scientific name
species_list = [
    {"key": 2432389, "name": "Myotis nattereri"},
    {"key": 2432439, "name": "Myotis daubentonii"},
    {"key": 2433753, "name": "Lutra lutra"},
    {"key": 2433875, "name": "Meles meles"},
    {"key": 2434793, "name": "Phoca vitulina"},
    {"key": 2434806, "name": "Halichoerus grypus"},
    {"key": 2434816, "name": "Erignathus barbatus"},
    {"key": 2435767, "name": "Neomys fodiens"},
    {"key": 2436756, "name": "Lepus timidus"},
    {"key": 2436940, "name": "Oryctolagus cuniculus"},
    {"key": 2437760, "name": "Apodemus sylvaticus"},
    {"key": 2438616, "name": "Microtus agrestis"},
    {"key": 2439261, "name": "Rattus norvegicus"},
    {"key": 2440954, "name": "Cervus nippon"},
    {"key": 2440958, "name": "Cervus elaphus"},
    {"key": 4265185, "name": "Arvicola amphibius"},
    {"key": 5218465, "name": "Pipistrellus pipistrellus"},
    {"key": 5218507, "name": "Plecotus auritus"},
    {"key": 5218823, "name": "Mustela vison"},
    {"key": 5218878, "name": "Martes martes"},
    {"key": 5218911, "name": "Mustela putorius"},
    {"key": 5218987, "name": "Mustela nivalis"},
    {"key": 5219019, "name": "Mustela erminea"},
    {"key": 5219243, "name": "Vulpes vulpes"},
    {"key": 5219616, "name": "Erinaceus europaeus"},
    {"key": 5220126, "name": "Capreolus capreolus"},
    {"key": 5220136, "name": "Dama dama"},
    {"key": 5706764, "name": "Myodes glareolus"},
    {"key": 5707150, "name": "Pipistrellus pygmaeus"},
    {"key": 7429082, "name": "Mus musculus"},
    {"key": 7705930, "name": "Sus scrofa"},
    {"key": 7872906, "name": "Talpa europaea"},
    {"key": 7952072, "name": "Lepus europaeus"},
    {"key": 8316400, "name": "Sorex araneus"}
]

print("Fetching Wikipedia images and formatting them into whitelisted 500px thumbnails...")

sql_statements = []

for idx, sp in enumerate(species_list):
    key = sp["key"]
    name = sp["name"]
    print(f"[{idx+1}/34] Querying image for '{name}'...", end="", flush=True)

    url = f"https://en.wikipedia.org/w/api.php?action=query&prop=pageimages&format=json&piprop=original&titles={name}&redirects=1"
    headers = {"User-Agent": "CourseworkComplianceQA/2.0 (shaikbashah20@gmail.com; Edinburgh Napier University)"}

    try:
        r = requests.get(url, headers=headers, timeout=10)
        data = r.json()
        pages = data.get("query", {}).get("pages", {})
        
        image_url = None
        for page_id, page_data in pages.items():
            original = page_data.get("original", {})
            if original:
                image_url = original.get("source").split('?')[0]
                break
        
        if image_url:
            # Convert original URL to 500px thumbnail format
            # E.g. https://upload.wikimedia.org/wikipedia/commons/e/e6/Eurasian_otter.3.JPG
            # To: https://upload.wikimedia.org/wikipedia/commons/thumb/e/e6/Eurasian_otter.3.JPG/500px-Eurasian_otter.3.JPG
            parts = image_url.split("wikipedia/commons/")
            if len(parts) == 2:
                subpath = parts[1]
                filename = subpath.split("/")[-1]
                thumb_url = f"https://upload.wikimedia.org/wikipedia/commons/thumb/{subpath}/500px-{filename}"
                print(f" FOUND THUMB: {thumb_url}")
                escaped_url = thumb_url.replace("'", "''")
                sql_statements.append(f"UPDATE species SET image_url = '{escaped_url}' WHERE gbif_species_key = {key};")
            else:
                print(f" FOUND RAW: {image_url}")
                escaped_url = image_url.replace("'", "''")
                sql_statements.append(f"UPDATE species SET image_url = '{escaped_url}' WHERE gbif_species_key = {key};")
        else:
            print(" FAILED: No original image found in page metadata.")
            
    except Exception as e:
        print(f" ERROR: {str(e)}")
    
    time.sleep(1.0)

# Write to database/update_images.sql
sql_filepath = "database/update_images.sql"
with open(sql_filepath, "w") as f:
    f.write("-- Automated Wikipedia 500px whitelisted thumbnail updates\n")
    f.write("USE scottish_mammals;\n\n")
    for statement in sql_statements:
        f.write(statement + "\n")

print(f"\nCompleted! Generated {len(sql_statements)} statements in '{sql_filepath}'")
