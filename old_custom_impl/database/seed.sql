-- Seed Data for Scottish Mammal Observations Platform
-- MySQL 8.0+ / MariaDB compatible

-- Seed Users (Password is 'Highlands2026!' hashed using PHP's password_hash/bcrypt)
-- Let's use the hash: $2y$12$PXBoN7Kl7FHBKsLGswlWF.l5YjAfEca1TOXZmnAcQaW70cZFWv37K
INSERT INTO users (username, email, password_hash, role) VALUES
('admin', 'admin@scottishmammals.org.uk', '$2y$12$PXBoN7Kl7FHBKsLGswlWF.l5YjAfEca1TOXZmnAcQaW70cZFWv37K', 'admin'),
('wildliferose', 'rose@nature.org.uk', '$2y$12$PXBoN7Kl7FHBKsLGswlWF.l5YjAfEca1TOXZmnAcQaW70cZFWv37K', 'user');

-- Seed Species
INSERT INTO species (id, common_name, scientific_name, habitat, conservation_status, description, diet, lifespan, average_weight, image_path) VALUES
(
    1,
    'Red Squirrel',
    'Sciurus vulgaris',
    'Coniferous and Mixed Woodland',
    'Near Threatened (UK)',
    'The red squirrel is Scotland\'s only native squirrel species, easily distinguished by its reddish-brown fur, tufted ears, and long, bushy tail. They spend most of their time in the forest canopy, leaping agilely between branches. In Scotland, their main stronghold is in the Highlands and Dumfries & Galloway, where they are protected from competition with the non-native grey squirrel.',
    'Seeds, pine cones, acorns, hazelnuts, berries, fungi, and occasionally sap.',
    '3 - 5 years',
    '250 - 350 g',
    'red_squirrel.jpg'
),
(
    2,
    'Pine Marten',
    'Martes martes',
    'Ancient Woodland and Rocky Crags',
    'Least Concern (Recovering)',
    'A cat-sized member of the weasel family, the pine marten has rich brown fur, a prominent creamy-yellow throat patch, and a long, bushy tail. Known for their incredible agility, they are expert tree climbers, often hunting in the canopy. Once near extinction in Britain, their populations are making a significant recovery in northern and central Scotland.',
    'Small mammals (especially voles), birds, insects, eggs, fruit, and berries.',
    '8 - 10 years',
    '1.2 - 2.2 kg',
    'pine_marten.jpg'
),
(
    3,
    'Scottish Wildcat',
    'Felis silvestris',
    'Forest Margins and Moorland Scrubs',
    'Critically Endangered (UK)',
    'Often referred to as the \'Highland Tiger\', the Scottish wildcat is our only remaining native feral cat species. It looks similar to a large tabby domestic cat, but is sturdier, has longer limbs, a larger flat-topped head, and a distinctive thick, blunt tail with black rings and a black tip. It is extremely rare, facing major threats from hybridisation with domestic cats.',
    'Rabbits, voles, mice, hares, and ground-nesting birds.',
    '10 - 12 years',
    '3.5 - 7.5 kg',
    'wildcat.jpg'
),
(
    4,
    'Red Deer',
    'Cervus elaphus',
    'Open Moorland, Glens, and Woodlands',
    'Least Concern',
    'The red deer is Britain\'s largest land mammal and an iconic symbol of the Scottish Highlands. Stags possess magnificent branched antlers, which grow and shed annually. They are highly social animals, forming large herds. During the autumn breeding season (the rut), the glens echo with the deep, guttural roars of stags defending their harems.',
    'Grasses, sedges, heather, tree shoots, leaves, and bark.',
    '15 - 18 years',
    '100 - 240 kg',
    'red_deer.jpg'
),
(
    5,
    'Eurasian Beaver',
    'Castor fiber',
    'Freshwater Lochs, Rivers, and Wetlands',
    'Endangered (UK)',
    'The Eurasian beaver is a large, semi-aquatic rodent and a keystone species, famous for building dams, digging canals, and creating rich wetland habitats. They have dense waterproof brown fur, webbed hind feet, and a broad, flat, scaly tail used for swimming and signaling danger by slapping the water. Reintroduced to Scotland, they are now protected.',
    '100% vegetarian; bark, twigs, shoots, leaves, and aquatic plants.',
    '10 - 15 years',
    '18 - 30 kg',
    'beaver.jpg'
),
(
    6,
    'Eurasian Otter',
    'Lutra lutra',
    'Rivers, Lochs, and Coastal Waters',
    'Near Threatened',
    'The Eurasian otter is a sleek, semi-aquatic carnivore, perfectly adapted for swimming with its streamlined body, webbed feet, and thick, muscular tail (rudder). In Scotland, they inhabit both freshwater river systems and marine coastlines (particularly in Shetland and the west coast), where they are often active during the day.',
    'Fish (e.g., eels, salmonids), amphibians, crabs, and waterbirds.',
    '5 - 8 years',
    '6 - 10 kg',
    'otter.jpg'
),
(
    7,
    'Harbour Seal',
    'Phoca vitulina',
    'Coastal Waters and Sandbanks',
    'Least Concern',
    'Also known as the common seal, this marine mammal has a rounded head, a V-shaped nostril profile, and a mottled grey-brown coat. They are commonly seen resting on sandbanks, mudflats, or rocky shores in a characteristic \'banana\' posture (with head and tail raised). Scotland holds a significant portion of the European population.',
    'Fish (cod, herring, flatfish), squid, and octopus.',
    '20 - 30 years',
    '80 - 130 kg',
    'harbour_seal.jpg'
);

-- Seed Observations
-- We map these to Cairngorms, Loch Lomond, Isle of Mull, Galloway, Edinburgh, Knapdale, etc.
INSERT INTO observations (species_id, observer_name, observation_date, latitude, longitude, location_name, notes, status) VALUES
(1, 'Dr. Fiona Campbell', '2026-07-12', 57.14520000, -3.67480000, 'Rothiemurchus Forest, Cairngorms', 'Spotted a healthy adult red squirrel gathering pine seeds near the visitor centre path. Very active.', 'approved'),
(1, 'Marcus Thorne', '2026-07-28', 56.24150000, -4.61250000, 'Balmaha, Loch Lomond', 'Sighted high in an old oak tree near the West Highland Way path. Disturbed by a dog.', 'approved'),
(1, 'Emily Watson', '2026-08-02', 55.04890000, -4.48210000, 'Galloway Forest Park', 'Spotted at the wildlife hide feeder. Had reddish-brown coat and long tail tufts.', 'approved'),

(2, 'Dr. Fiona Campbell', '2026-06-18', 57.18950000, -3.82140000, 'Aviemore Woods, Cairngorms', 'Spotted at dusk traversing a fallen pine trunk. Throat patch was light cream, very distinct.', 'approved'),
(2, 'Douglas Ross', '2026-07-05', 56.55120000, -5.48910000, 'Loch Sunart, Ardnamurchan', 'Briefly crossed the single-track road ahead of my vehicle around 11:30 PM. Beautiful bushy tail.', 'approved'),

(3, 'Alastair Graham', '2026-05-22', 57.34560000, -3.98760000, 'Monadhliath Mountains', 'Remote camera trap capture. Confirmed wildcat with broad flat head and ringed, blunt tail. Minimal hybrid markers visible.', 'approved'),
(3, 'Dr. Fiona Campbell', '2026-07-15', 57.26890000, -3.51250000, 'Glenlivet Estate, Moray', 'Visual sighting near a gorse thicket at dawn. Sturdy build, thick ringed tail. Magnificent specimen.', 'approved'),

(4, 'Callum MacDonald', '2026-06-30', 57.11200000, -3.58900000, 'Glenmore Glen, Cairngorms', 'A herd of approximately 25 stags grazing in the open glen below the peaks. Antlers in velvet.', 'approved'),
(4, 'Sarah Jenkins', '2026-07-20', 56.41240000, -4.21850000, 'Glen Coe Valley', 'Two stags standing near the roadside riverbank. Magnificent size. Unfazed by distant hikers.', 'approved'),
(4, 'Robert Burns', '2026-08-10', 57.31120000, -6.18560000, 'Sligachan Glen, Isle of Skye', 'Large stag spotted grazing on the heather slopes in late afternoon sun. Majestic antlers.', 'approved'),

(5, 'Dr. Fiona Campbell', '2026-06-05', 56.03120000, -5.58910000, 'River Add, Knapdale Forest', 'Watched a beaver swimming with twigs in its mouth. Tail slapping heard twice when I moved.', 'approved'),
(5, 'Isobel Stewart', '2026-07-14', 56.40230000, -3.42150000, 'River Tay, near Dunkeld', 'Dam structure visible and fresh chew marks on willows. Saw one adult beaver swimming at dusk.', 'approved'),

(6, 'Neil MacLeod', '2026-07-01', 56.44210000, -6.04210000, 'Loch Na Keal, Isle of Mull', 'Two otters foraging in the kelp beds. Swimming synchronously, diving, and surfacing with small crabs.', 'approved'),
(6, 'Dr. Fiona Campbell', '2026-07-29', 57.48910000, -4.20120000, 'North Kessock, Beauly Firth', 'Spotted swimming close to shore. Divot-like head shape visible. Seemed to be hunting eels.', 'approved'),
(6, 'Eilidh Munro', '2026-08-11', 55.93210000, -3.22150000, 'Water of Leith, Edinburgh', 'Spotted near the Dean Village bridge in the early morning. Diving repeatedly. Amazing urban sighting!', 'approved'),

(7, 'Sarah Jenkins', '2026-07-10', 56.38120000, -6.15120000, 'Calgary Bay, Isle of Mull', 'Around 15 seals hauled out on the rocky reefs at low tide. Banana-pose posture observed.', 'approved'),
(7, 'Thomas Logan', '2026-08-01', 57.25120000, -4.42150000, 'Loch Ness shore near Urquhart Castle', 'Spotted one seal swimming close to the rocky shoreline, head visible above water. V-nostrils seen clearly.', 'approved');
