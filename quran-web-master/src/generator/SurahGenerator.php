<?php
require __DIR__ . '/SurahGenerator.php';
define('BASE_DIR', realpath(__DIR__ . '/../..'));

function env($envName, $default = null)
{
    if (isset($_SERVER[$envName])) {
        return $_SERVER[$envName];
    }

    return $default;
}

function loadXmlData($filePath) {
    if (file_exists($filePath)) {
        $xml = simplexml_load_file($filePath);
        if ($xml === false) {
            throw new Exception("Failed to load XML file: " . $filePath);
        }
        return $xml;
    } else {
        throw new Exception("XML file not found: " . $filePath);
    }
}

function getMetadata($xml, $suraIndex) {
    $sura = $xml->sura[$suraIndex];
    
    $index = (string)$sura['index'];
    $ayas = (string)$sura['ayas'];
    $name = (string)$sura['name'];
    $tname = (string)$sura['tname'];
    $type = (string)$sura['type'];

    return [$index, $ayas, $name, $tname, $type];
}

function generateSuraHtml($quranXml, $suraIndex) {
    $sura = $quranXml->sura[$suraIndex];
    $txt = "<ol id=\"sura-ol\">";

    foreach ($sura->children() as $i => $aya) {
        $txt .= "<li class=\"sura-li\">{$aya['text']}</li>
                 <p id=\"t-{$i}\"></p>
                 <hr>";
    }

    $txt .= "</ol>";
    return $txt;
}

function generateSidebarButtons($quranDataXml) {
    $txt = "";
    foreach ($quranDataXml->sura as $sura) {
        $index = (string)$sura['index'];
        $tname = (string)$sura['tname'];
        $txt .= "<a class=\"side-buttons\" href=\"#\" onclick=\"load({$index}, tIndex); updateZoom()\"><i class=\"fa-solid fa-book-quran\"></i> {$index}: {$tname}</a>";
    }
    return $txt;
}

$config = [
    'quranXmlPath' => env('QURAN_XML_PATH', BASE_DIR . '/data/quran-uthmani.xml'),
    'quranDataXmlPath' => env('QURAN_DATA_XML_PATH', BASE_DIR . '/data/quran-data.xml'),
    'translations' => [
        'en' => env('QURAN_TRANSLATION_EN', BASE_DIR . '/data/en.sahih.xml'),
        'id' => env('QURAN_TRANSLATION_ID', BASE_DIR . '/data/id.indonesian.xml')
    ],
    'baseUrl' => env('QURAN_BASE_URL'),
    'baseMurottalUrl' => env('QURAN_BASE_MUROTTAL_URL'),
    'buildDir' => BASE_DIR . '/build',
    'publicDir' => BASE_DIR . '/src/public',
    'templateDir' => env('QURAN_TEMPLATE_DIR', BASE_DIR . '/src/generator/template'),
    'beginSurah' => env('QURAN_BEGIN_SURAH', 1),
    'endSurah' => env('QURAN_END_SURAH', 114),
    'githubProjectUrl' => env('QURAN_GITHUB_PROJECT_URL', 'https://github.com/rioastamal/quran-web'),
    'rawHtmlMeta' => env('QURAN_RAW_HTML_META'),
    'ogImageUrl' => env('QURAN_OG_IMAGE_URL', 'https://s3-ap-southeast-1.amazonaws.com/quranweb/quranweb-1024.png')
];

echo "Generating website...";
try {
    $config['baseMurottalUrl'] = $config['baseMurottalUrl'] ? $config['baseMurottalUrl'] : 'https://everyayah.com/data';

    // Load XML data
    $quranXml = loadXmlData($config['quranXmlPath']);
    $quranDataXml = loadXmlData($config['quranDataXmlPath']);

    // Initialize SurahGenerator
    $generator = new SurahGenerator($config);
    
    // Copy public files
    $generator->copyPublic();

    // Generate Surah pages
    for ($i = $config['beginSurah'] - 1; $i < $config['endSurah']; $i++) {
        $suraIndex = $i;
        $metadata = getMetadata($quranDataXml, $suraIndex);
        $suraHtml = generateSuraHtml($quranXml, $suraIndex);
        $sidebarButtons = generateSidebarButtons($quranDataXml);

        // You can now use $suraHtml and $sidebarButtons in your template or further processing
        // This part depends on how SurahGenerator is implemented, so you might need to adjust it accordingly
        // For example, if SurahGenerator expects JSON data, you might need to refactor it to accept XML data or processed HTML
    }

    echo "done.\n";
} catch (Exception $e) {
    echo "FAIL.\n";
    printf("Error: %s\n", $e->getMessage());
}