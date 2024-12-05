<?php
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$message = strtolower(trim($input['message']));

// Function to check if message contains any keywords from an array
function containsKeywords($message, $keywords) {
    foreach ($keywords as $keyword) {
        if (strpos($message, strtolower($keyword)) !== false) {
            return true;
        }
    }
    return false;
}

// Main category keywords
$main_categories = [
    'food' => ['food', 'delicacies', 'dish', 'eat', 'bulalo', 'lomi', 'goto', 'tapa', 'adobo', 'cuisine', '🍲'],
    'coffee' => ['coffee', 'barako', 'kapeng', 'brew', 'beans', '☕'],
    'handicraft' => ['handicraft', 'craft', 'balisong', 'knife', 'embroidery', 'pottery', 'souvenir', '🎨'],
    'festival' => ['festival', 'fiesta', 'celebration', 'event', '🎉'],
    'marine' => ['marine', 'fish', 'seafood', 'tawilis', 'tulingan', 'bangus', '🐟'],
    'industry' => ['industry', 'industries', 'local industry', 'business', '🏺']
];

// Welcome message for unrecognized input
$welcome_message = "👋 Hi! I can help you with these topics about Batangas:

1. 🍲 Local Food & Delicacies
2. ☕ Kapeng Barako
3. 🎨 Traditional Handicrafts
4. 🎉 Festivals & Events
5. 🐟 Marine Products
6. 🏺 Local Industries

Please choose one of these topics or ask a specific question about them!";

// Check if message contains any main category keywords
$found_category = false;
foreach ($main_categories as $category => $keywords) {
    if (containsKeywords($message, $keywords)) {
        $found_category = true;
        break;
    }
}

if (!$found_category) {
    $response = "I apologize, but I can only provide information about these specific topics:

1. 🍲 Local Food & Delicacies
2. ☕ Kapeng Barako
3. 🎨 Traditional Handicrafts
4. 🎉 Festivals & Events
5. 🐟 Marine Products
6. 🏺 Local Industries

Please choose one of these topics or rephrase your question to match these categories. How can I help you learn about Batangas?";

    echo json_encode([
        'status' => 'success',
        'message' => $response
    ]);
    exit;
}

// Your existing response logic for valid categories...
if (containsKeywords($message, $main_categories['food'])) {
    $response = "🍲 Batangas Cuisine & Delicacies:

Signature Dishes:
1. Bulalo
   • Premium beef shanks and bone marrow soup
   • Best in Mahogany Market, Tagaytay
   • Served with corn, pechay, and potatoes

2. Lomi Batangas
   • Thick noodles in rich broth
   • Famous in Lipa City
   • Known for generous toppings

3. Goto Batangas
   • Different from regular goto
   • Uses beef tripe and innards
   • Served with chili garlic and calamansi

4. Tapang Taal
   • Special cured beef from Taal
   • Marinated in native ingredients
   • Best paired with Kapeng Barako

5. Sinaing na Tulingan
   • Slow-cooked mackerel in clay pot
   • Traditional Batangueño dish
   • Uses rock salt and dried kamias

Local Delicacies:
• Panutsa (Peanut brittle from Malvar)
• Suman Batangas (Special rice cake in banana leaves)
• Tamales (Rice and coconut wrapped in banana leaves)
• Uraro (Arrowroot cookies from Taal)
• Bibingkoy (Glutinous rice balls in ginger soup)
• Tsokolate Tablea (Local chocolate tablets)

Where to Find:
• Mahogany Market, Tagaytay
• Lipa City Public Market
• Taal Heritage Town
• Lemery Food District
• Nasugbu Restaurant Strip";
}

elseif (containsKeywords($message, $main_categories['coffee'])) {
    $response = "☕ Kapeng Barako - Batangas' Pride:

About Barako Coffee:
• Liberica coffee variety (Coffea liberica)
• Grown in Batangas highlands
• Known for strong, full-bodied flavor
• Part of Philippine coffee heritage

Characteristics:
• Strong woody aroma
• Full-bodied, bold taste
• Lower acidity than Arabica
• Distinct earthy notes
• Larger coffee beans

Growing Areas:
• Lipa City
• San Jose
• Mabini
• Lemery
• Taal

Traditional Preparation:
1. Fresh ground beans
2. Hot water (90-96°C)
3. Traditional cloth strainer
4. Served black or with muscovado sugar

Where to Experience:
• Lipa Coffee Farms
• Local Markets (Best early morning)
• Traditional Coffee Shops
• Farm Tours Available
• Pasalubong Centers";
}

elseif (containsKeywords($message, $main_categories['handicraft'])) {
    $response = "🎨 Batangas Traditional Handicrafts:

    1. Balisong (Butterfly Knife)
       • Handcrafted in Taal since 1900s
       • Made by master craftsmen
       • Various designs and sizes
       • Traditional forging techniques
       • Cultural symbol of Batangas
    
    2. Taal Embroidery
       • UNESCO-recognized craft
       • Detailed hand embroidery
       • Used in Barong Tagalog
       • Callado technique
       • Generations-old patterns
    
    3. Pottery & Palayok
       • San Juan pottery tradition
       • Clay from local sources
       • Traditional firing methods
       • Functional and decorative pieces
       • Eco-friendly craftsmanship
    
    4. Traditional Crafts:
       • Bamboo crafts (Baskets, furniture)
       • Wood carving (Santos, furniture)
       • Basket weaving (Tampipi, Bayong)
       • Pamaypay (Hand fans)
       • Banig (Traditional mats)
       • Crochet lace making
    
    Craft Centers:
    • Taal Heritage Town
    • San Juan Pottery Village
    • Lipa Arts & Crafts Center
    • Local Markets
    
    Best Time to Visit:
    • During town fiestas
    • Heritage festivals
    • Morning markets";
}

elseif (containsKeywords($message, $main_categories['marine'])) {
    $response = "🐟 Batangas Marine Products:

    Endemic Species:
    1. Tawilis - Found only in Taal Lake
    2. Maliputo - Premium local fish
    
    Popular Seafood:
    • Tulingan (Mackerel)
    • Bangus (Milkfish)
    • Talaba (Oysters)
    • Tahong (Mussels)
    
    Processed Products:
    • Bagoong Balayan
    • Dried Fish
    • Tinapa (Smoked fish)
    
    Best Markets:
    - Lemery Fish Port
    - Balayan Bay area
    - Nasugbu markets";
}

elseif (containsKeywords($message, $main_categories['festival'])) {
    $response = "🎉 Major Batangas Festivals:

    1. Lambayok Festival (San Juan)
    - Celebrates pottery, lambanog, and fishing
    
    2. Kabakahan Festival (Padre Garcia)
    - Grand cattle trading festival
    
    3. Sublian Festival
    - Traditional Subli dance celebration
    
    4. Parada ng Lechon (Balayan)
    - Famous roasted pig parade
    
    5. El Pasubat Festival
    - Showcasing local products
    
    Which festival would you like to know more about?";
}

elseif (containsKeywords($message, $main_categories['industry'])) {
    $response = "🏺 Major Industries in Batangas:

    Agriculture & Livestock:
    • Padre Garcia - Cattle Trading Capital of the Philippines
        - Largest livestock market in the country
        - Weekly cattle auctions
        - Modern trading facilities
        - Quality breeding programs
    
    Marine Industry:
    • Lemery Fish Port
        - Major fishing hub
        - Seafood processing
        - Fresh catch daily
    
    • Balayan Bay Area
        - Commercial fishing
        - Fish processing plants
        - Bagoong production
    
    Coffee Industry:
    • Lipa City
        - Historical coffee capital
        - Barako coffee production
        - Coffee processing facilities
    
    Tourism & Hospitality:
    • Nasugbu
        - Beach resorts
        - Water sports
        - Tourism facilities
    
    Traditional Industries:
    • Taal
        - Balisong crafting
        - Embroidery
        - Heritage products
    
    • San Juan
        - Pottery making
        - Clay products
        - Traditional crafts

    Manufacturing Zones:
    • LIMA Technology Center
    • First Philippine Industrial Park
    • Light Industry & Science Park
    
    Best Time to Visit:
    • Cattle Trading: Tuesday & Friday (Padre Garcia)
    • Fish Ports: Early morning (3-7 AM)
    • Traditional Crafts: Weekday mornings
    • Industrial Parks: Business hours";
}

else {
    $response = $welcome_message;
}

echo json_encode([
    'status' => 'success',
    'message' => $response
]);
?> 