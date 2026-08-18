<?php
/**
 * Database Migration - Populate Species Image URLs
 * SET08101 Web Technologies Coursework
 */

require_once __DIR__ . '/../includes/db.php';

$imageMapping = [
    2432389 => 'https://upload.wikimedia.org/wikipedia/commons/thumb/c/cc/Myotis_nattereri_01.jpg/640px-Myotis_nattereri_01.jpg', // Natterer's Bat
    2432439 => 'https://upload.wikimedia.org/wikipedia/commons/thumb/f/f6/Water_bat_daubentons.jpg/640px-Water_bat_daubentons.jpg', // Daubenton's Bat
    2433753 => 'https://upload.wikimedia.org/wikipedia/commons/thumb/1/1a/LutraLutra_crop.jpg/640px-LutraLutra_crop.jpg', // European Otter
    2433875 => 'https://upload.wikimedia.org/wikipedia/commons/thumb/c/ca/Meles_meles_sitting.jpg/640px-Meles_meles_sitting.jpg', // European Badger
    2434793 => 'https://upload.wikimedia.org/wikipedia/commons/thumb/2/25/Common_seal_Phoca_vitulina.jpg/640px-Common_seal_Phoca_vitulina.jpg', // Harbour Seal
    2434806 => 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/8e/Grey_Seal_Halichoerus_grypus.jpg/640px-Grey_Seal_Halichoerus_grypus.jpg', // Grey Seal
    2434816 => 'https://upload.wikimedia.org/wikipedia/commons/thumb/c/cb/Bearded_seal_face_shot.jpg/640px-Bearded_seal_face_shot.jpg', // Bearded Seal
    2435767 => 'https://upload.wikimedia.org/wikipedia/commons/thumb/5/58/Water_shrew.jpg/640px-Water_shrew.jpg', // Water Shrew
    2436756 => 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/87/Mountain_Hare_Cairngorms.jpg/640px-Mountain_Hare_Cairngorms.jpg', // Mountain Hare
    2436940 => 'https://upload.wikimedia.org/wikipedia/commons/thumb/3/37/Oryctolagus_cuniculus_Tasmania_2.jpg/640px-Oryctolagus_cuniculus_Tasmania_2.jpg', // European Rabbit
    2437760 => 'https://upload.wikimedia.org/wikipedia/commons/thumb/3/36/Apodemus_sylvaticus_crop.jpg/640px-Apodemus_sylvaticus_crop.jpg', // Wood Mouse
    2438616 => 'https://upload.wikimedia.org/wikipedia/commons/thumb/4/46/Microtus_agrestis.jpg/640px-Microtus_agrestis.jpg', // Field Vole
    2439261 => 'https://upload.wikimedia.org/wikipedia/commons/thumb/f/f3/Brown_rat_in_a_wetland.jpg/640px-Brown_rat_in_a_wetland.jpg', // Brown Rat
    2440954 => 'https://upload.wikimedia.org/wikipedia/commons/thumb/0/0c/Sika_Deer_stag.jpg/640px-Sika_Deer_stag.jpg', // Sika Deer
    2440958 => 'https://upload.wikimedia.org/wikipedia/commons/thumb/0/03/Red_deer_stag_at_Glen_Etive%2C_Scotland.jpg/640px-Red_deer_stag_at_Glen_Etive%2C_Scotland.jpg', // Red Deer
    4265185 => 'https://upload.wikimedia.org/wikipedia/commons/thumb/d/db/Water_Vole_Arvicola_amphibius.jpg/640px-Water_Vole_Arvicola_amphibius.jpg', // Water Vole
    5218465 => 'https://upload.wikimedia.org/wikipedia/commons/thumb/c/cd/PipistrellusPipistrellus.jpg/640px-PipistrellusPipistrellus.jpg', // Common Pipistrelle
    5218507 => 'https://upload.wikimedia.org/wikipedia/commons/thumb/6/6f/Plecotus_auritus01.jpg/640px-Plecotus_auritus01.jpg', // Brown Long-eared Bat
    5218823 => 'https://upload.wikimedia.org/wikipedia/commons/thumb/3/38/American_Mink_in_New_York.jpg/640px-American_Mink_in_New_York.jpg', // American Mink
    5218878 => 'https://upload.wikimedia.org/wikipedia/commons/thumb/c/c5/Baummarder_01.jpg/640px-Baummarder_01.jpg', // Pine Marten
    5218911 => 'https://upload.wikimedia.org/wikipedia/commons/thumb/6/68/Mustela_putorius.jpg/640px-Mustela_putorius.jpg', // European Polecat
    5218987 => 'https://upload.wikimedia.org/wikipedia/commons/thumb/f/f5/Mustela_nivalis_-british_wildlife_centre-8.jpg/640px-Mustela_nivalis_-british_wildlife_centre-8.jpg', // Weasel
    5219019 => 'https://upload.wikimedia.org/wikipedia/commons/thumb/5/52/Stoat_in_winter_coat.jpg/640px-Stoat_in_winter_coat.jpg', // Stoat
    5219243 => 'https://upload.wikimedia.org/wikipedia/commons/thumb/1/16/Red_fox_in_winter.jpg/640px-Red_fox_in_winter.jpg', // Red Fox
    5219616 => 'https://upload.wikimedia.org/wikipedia/commons/thumb/c/cb/Erinaceus_europaeus_%28cropped%29.jpg/640px-Erinaceus_europaeus_%28cropped%29.jpg', // European Hedgehog
    5220126 => 'https://upload.wikimedia.org/wikipedia/commons/thumb/e/e3/Capreolus_capreolus_p.jpg/640px-Capreolus_capreolus_p.jpg', // Roe Deer
    5220136 => 'https://upload.wikimedia.org/wikipedia/commons/thumb/5/52/Dama_dama_lying.jpg/640px-Dama_dama_lying.jpg', // Fallow Deer
    5706764 => 'https://upload.wikimedia.org/wikipedia/commons/thumb/5/5a/Clethrionomys_glareolus.jpg/640px-Clethrionomys_glareolus.jpg', // Bank Vole
    5707150 => 'https://upload.wikimedia.org/wikipedia/commons/thumb/2/21/Pipistrellus_pygmaeus_1.jpg/640px-Pipistrellus_pygmaeus_1.jpg', // Soprano Pipistrelle
    7429082 => 'https://upload.wikimedia.org/wikipedia/commons/thumb/0/0d/House_mouse.jpg/640px-House_mouse.jpg', // House Mouse
    7705930 => 'https://upload.wikimedia.org/wikipedia/commons/thumb/f/f0/Wild_Boar_Herv%C3%A9_Geraud.jpg/640px-Wild_Boar_Herv%C3%A9_Geraud.jpg', // Wild Boar
    7872906 => 'https://upload.wikimedia.org/wikipedia/commons/thumb/1/1c/Talpa_europaea_1.jpg/640px-Talpa_europaea_1.jpg', // European Mole
    7952072 => 'https://upload.wikimedia.org/wikipedia/commons/thumb/1/1c/Lepus_europaeus_1.jpg/640px-Lepus_europaeus_1.jpg', // Brown Hare
    8316400 => 'https://upload.wikimedia.org/wikipedia/commons/thumb/1/14/Sorex_araneus_standing.jpg/640px-Sorex_araneus_standing.jpg' // Common Shrew
];

try {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare('UPDATE species SET image_url = :url WHERE gbif_species_key = :key');
    
    $pdo->beginTransaction();
    foreach ($imageMapping as $key => $url) {
        $stmt->execute([':url' => $url, ':key' => $key]);
    }
    $pdo->commit();
    echo "Successfully updated " . count($imageMapping) . " species image URLs in database.\n";
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die("Migration failed: " . $e->getMessage() . "\n");
}
