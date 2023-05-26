<?php
require_once('./vendor/autoload.php');

use PhpSchool\CliMenu\CliMenu;
use PhpSchool\CliMenu\Builder\CliMenuBuilder;
use PhpSchool\CliMenu\MenuItem\AsciiArtItem;
use PhpSchool\CliMenu\Action\GoBackAction;
use PhpSchool\CliMenu\Action\ExitAction;

use Automattic\WooCommerce\Client;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

function ascii_message($mess, $fgcolor, $bgcolor)
{

    $art = <<<ART
        $mess
    ART;

    $menu = (new CliMenuBuilder)
        ->setForegroundColour($fgcolor)
        ->setBackgroundColour($bgcolor)
        ->addAsciiArt($art, AsciiArtItem::POSITION_CENTER)
        ->addItem('Go Back', new GoBackAction)
        ->setMarginAuto()
        ->build();

    $menu->open();
}

$woocommerce = new Client(
    'https://fabianamecca.it/',
    'ck_5ae8f3e0a2a1fdb664b23ae7b7652d6d649ced17',
    'cs_1490a610d7163cff36e2cb3ec0eaa6dd8e968482',
    [
        'wp_api' => true,
        'version' => 'wc/v3',
        'query_string_auth' => true
    ]
);
$itemCallable = function (CliMenu $menu) {
    echo $menu->getSelectedItem()->getText();
};
$art = <<<ART
        ______             ________            _____                      
        ___  / ______      __  ___/_______________(_)______ _____________ 
        __  /  _  __ \     _____ \_  ___/_  ___/_  /__  __ `/_  __ \  __ \
        _  /___/ /_/ /     ____/ // /__ _  /   _  / _  /_/ /_  / / / /_/ /
        /_____/\____/      /____/ \___/ /_/    /_/  _\__, / /_/ /_/\____/ 
                                                    /____/                
                                                    

ART;

$menu = (new CliMenuBuilder)

    ->setForegroundColour('yellow')
    ->setBackgroundColour('black')
    ->addAsciiArt($art, AsciiArtItem::POSITION_CENTER)
    ->addSubMenu('Inserisci categoria', function (CliMenuBuilder $b) {

        $b->setTitle('Inserisci categoria > Options')
            ->addItem('Inserisci il nome della categoria: ', function (CliMenu $menu) {
                $result = $menu->askText()
                    ->setPromptText('Inserisci nome categoria')
                    ->setPlaceholderText('es. Anelli')
                    ->setValidationFailedText('Campo obbligatorio')
                    ->ask();

                $data = [
                    'name' => $result->fetch(),
                ];
                global $woocommerce;
                $woocommerce->post('products/categories', $data);
            });
    })
    ->addSubMenu('Inserisci attributo', function (CliMenuBuilder $b) {
        $b->setTitle('Inserisci attributo > Options')
            ->addItem('Inserisci attributo: ', function (CliMenu $menu) {

                $nomeAttr = $menu->askText()
                    ->setPromptText('Inserisci nome attributo')
                    ->setValidationFailedText('Campo obbligatorio')
                    ->ask();

                $slug = $menu->askText()
                    ->setPromptText('Inserisci nome slug')
                    ->setPlaceholderText('minuscolo')
                    ->setValidationFailedText('Campo obbligatorio')
                    ->ask();

                $data = [
                    'name' => $nomeAttr->fetch(),
                    'slug' => $slug->fetch(),
                    'type' => 'select',
                    'order_by' => 'menu_order',
                    'has_archives' => true
                ];
                global $woocommerce;
                $woocommerce->post('products/attributes', $data);
            });
    })
    ->addSubMenu('Inserisci prodotto singolo', function (CliMenuBuilder $b) {
        $b->setTitle('Inserisci prodotto singolo > Options')
            ->addItem('Inserisci prodotto singolo', function (CliMenu $menu) {

                global $woocommerce;

                $arrayProdotti = $woocommerce->get('products', ['per_page' => '100']);
                //echo var_dump($arrayProdotti, true);

                $nomePr = $menu->askText()
                    ->setPromptText('Inserisci il nome del prodotto:')
                    ->setValidationFailedText('Campo obbligatorio')
                    ->ask();
                while (array_search($nomePr->fetch(), array_column((array)$arrayProdotti, 'name'))) {
                    $nomePr = $menu->askText()
                        ->setPromptText('Inserisci il nome del prodotto:')
                        ->setPlaceholderText('Nome gia presente, inserirne uno nuovo')
                        ->ask();
                }
                $prezzoPr = $menu->askText()
                    ->setPromptText('Inserisci il prezzo con il punto:')
                    ->ask();

                while (empty($prezzoPr->fetch())) {

                    $prezzoPr = $menu->askText()
                        ->setPromptText('Inserisci il prezzo con il punto:')
                        ->setValidationFailedText('Il campo deve essere compilato con numeri!!')
                        ->ask();
                }

                $prezzoPrFormat = number_format($prezzoPr->fetch(), 2, ".", "");

                $desLPr = $menu->askText()
                    ->setPromptText('Inserisci una descrizione lunga:')
                    ->setValidationFailedText('Campo obbligatorio')
                    ->ask();
                $desCPr = $menu->askText()
                    ->setPromptText('Inserisci una descrizione breve:')
                    ->setValidationFailedText('Campo obbligatorio')
                    ->ask();
                $qtPr = $menu->askNumber()
                    ->setPromptText('Inserisci quantita:')
                    ->setValidationFailedText('Campo obbligatorio')
                    ->ask();
                $skuPr = $menu->askText()
                    ->setPromptText('Inserisci codice sku:')
                    ->ask();

                //$key = array_search('100', array_column($userdb, 'uid'));  

                while (array_search($skuPr->fetch(), array_column((array)$arrayProdotti, 'sku'))) {
                    $skuPr = $menu->askText()
                        ->setPromptText('Inserisci codice sku:')
                        ->setPlaceholderText('Sku gia presente, inserirne uno nuovo')
                        ->ask();
                }
                $listCategorie = $woocommerce->get('products/categories');

                $arrCat = array();
                foreach ($listCategorie as $cat) {

                    $arrCat[] = $cat->id . ") " . $cat->name;
                }
                echo (implode("\n", $arrCat));
                $catPr = $menu->askText()
                    ->setPromptText('Inserisci id categoria:')
                    ->setValidationFailedText('Campo obbligatorio')
                    ->ask();
                $listAttributi = $woocommerce->get('products/attributes');

                $arrAttr = array();
                foreach ($listAttributi as $attr) {

                    $arrAttr[] = $attr->id . ") " . $attr->name;
                }
                echo (implode("\n", $arrAttr));
                $attPr = $menu->askText()
                    ->setPromptText('Inserisci id attributo:')
                    ->setValidationFailedText('Campo obbligatorio')
                    ->ask();
                $listTermini = $woocommerce->get('products/attributes/' . $attPr->fetch() . '/terms');

                $arrTerm = array();
                foreach ($listTermini as $term) {

                    $arrTerm[] = $term->id . ") " . $term->name;
                }
                echo (implode("\n", $arrTerm));
                $terminePr = $menu->askText()
                    ->setPromptText('Inserisci termine attributo:')
                    ->setValidationFailedText('Campo obbligatorio')
                    ->ask();
                $immagini = array();
                $imgPr = "";
                while (empty($imgPr)) {

                    $imgPr = $menu->askText()
                        ->setPromptText('Inserisci link immagine: ')
                        ->setValidationFailedText('Campo obbligatorio')
                        ->ask();

                    if (!empty($imgPr))
                        $immagini[] = ['src' => $imgPr->fetch()];
                }

                $ris_others_img = "Y";
                while ($ris_others_img == "Y") {

                    $ris_others_img = $menu->askText()
                        ->setPromptText('Vuoi inserire un altra immagine Y/N: ')
                        ->ask();

                    if ($ris_others_img->fetch() == "Y") {

                        $imgPr = "";
                        while (empty($imgPr)) {

                            $imgPr = $menu->askText()
                                ->setPromptText('Inserisci link immagine: ')
                                ->ask();

                            if (!empty($imgPr))
                                $immagini[] = ['src' => $imgPr->fetch()];
                        }
                    }
                }


                $data = [
                    'name' => $nomePr->fetch(),
                    'type' => 'simple',
                    'regular_price' => $prezzoPrFormat,
                    'description' => $desLPr->fetch(),
                    'short_description' => $desCPr->fetch(),
                    'manage_stock' => true,
                    'stock_quantity' => $qtPr->fetch(),
                    'sku' => $skuPr->fetch(),
                    'categories' => [
                        [
                            'id' => $catPr->fetch()
                        ]
                    ],
                    'images' => $immagini,
                    'attributes' =>
                    [
                        [
                            'id' => $attPr->fetch(),
                            'visible' => true,
                            'variation' => true,
                            'options' => [
                                $terminePr->fetch()
                            ]
                        ]
                    ]
                ];

                $res = $woocommerce->post('products', $data);
                echo print_r($res, true);

                if (empty($res->id)) {
                    ascii_message("Errore generico durante l'inserimento. (1)", "white", "red");
                } else if (!empty($res->id)) {
                    ascii_message("Prodotto inserito con successo", "white", "green");
                } else {
                    ascii_message("Errore generico durante l'inserimento. (2)", "white", "red");
                }
            });
    })
    ->addSubMenu('Inserisci prodotto variabile', function (CliMenuBuilder $b) {
        $b->setTitle('Inserisci prodotto variabile > Options')
            ->addItem('Inserisci prodotto variabile', function (CliMenu $menu) {

                global $woocommerce;
                $arrayProdotti = $woocommerce->get('products', ['per_page' => '100']);

                $nomePr = $menu->askText()
                    ->setPromptText('Inserisci il nome del prodotto:')
                    ->setValidationFailedText('Campo obbligatorio')
                    ->ask();
                while (array_search($nomePr->fetch(), array_column((array)$arrayProdotti, 'name'))) {
                    $nomePr = $menu->askText()
                        ->setPromptText('Inserisci il nome del prodotto:')
                        ->setPlaceholderText('Nome gia presente, inserirne uno nuovo')
                        ->ask();
                }
                $prezzoPr = $menu->askText()
                    ->setPromptText('Inserisci il prezzo con il punto:')
                    ->ask();

                while (empty($prezzoPr->fetch())) {

                    $prezzoPr = $menu->askText()
                        ->setPromptText('Inserisci il prezzo:')
                        ->setValidationFailedText('Il campo deve essere compilato con numeri!!')
                        ->ask();
                }
                $prezzoPrFormat = number_format($prezzoPr->fetch(), 2, ".", "");

                $desLPr = $menu->askText()
                    ->setPromptText('Inserisci una descrizione lunga:')
                    ->setValidationFailedText('Campo obbligatorio')
                    ->ask();
                $desCPr = $menu->askText()
                    ->setPromptText('Inserisci una descrizione breve:')
                    ->setValidationFailedText('Campo obbligatorio')
                    ->ask();
                $qtPr = $menu->askText()
                    ->setPromptText('Inserisci quantita:')
                    ->setValidationFailedText('Campo obbligatorio')
                    ->ask();
                $skuPr = $menu->askText()
                    ->setPromptText('Inserisci codice sku:')
                    ->setValidationFailedText('Campo obbligatorio')
                    ->ask();
                while (array_search($skuPr->fetch(), array_column((array)$arrayProdotti, 'sku'))) {
                    $skuPr = $menu->askText()
                        ->setPromptText('Inserisci codice sku:')
                        ->setPlaceholderText('Sku gia presente, inserirne uno nuovo')
                        ->ask();
                }
                $listCategorie = $woocommerce->get('products/categories');

                $arrCat = array();
                foreach ($listCategorie as $cat) {

                    $arrCat[] = $cat->id . ") " . $cat->name;
                }
                echo (implode("\n", $arrCat));
                $catPr = $menu->askText()
                    ->setPromptText('Inserisci id categoria:')
                    ->setValidationFailedText('Campo obbligatorio')
                    ->ask();
                $listAttributi = $woocommerce->get('products/attributes');

                $arrAttr = array();
                foreach ($listAttributi as $attr) {

                    $arrAttr[] = $attr->id . ") " . $attr->name;
                }
                echo (implode("\n", $arrAttr));
                $attPr = $menu->askText()
                    ->setPromptText('Inserisci id attributo:')
                    ->setValidationFailedText('Campo obbligatorio')
                    ->ask();
                $listTermini = $woocommerce->get('products/attributes/' . $attPr->fetch() . '/terms');

                $arrTerm = array();
                foreach ($listTermini as $term) {

                    $arrTerm[] = $term->id . ") " . $term->name;
                }
                echo (implode("\n", $arrTerm));
                $terminePr = $menu->askText()
                    ->setPromptText('Inserisci termine attributo:')
                    ->setPlaceholderText(implode(" - ", $arrTerm))
                    ->setValidationFailedText('Campo obbligatorio')
                    ->ask();
                $immagini = array();
                $imgPr = "";
                while (empty($imgPr)) {
                    $imgPr = $menu->askText()
                        ->setPromptText('Inserisci link immagine: ')
                        ->setValidationFailedText('Campo obbligatorio')
                        ->ask();

                    if (!empty($imgPr))
                        $immagini[] = ['src' => $imgPr->fetch()];
                }

                $ris_others_img = "Y";
                while ($ris_others_img == "Y") {

                    $ris_others_img = $menu->askText()
                        ->setPromptText('Vuoi inserire un altra immagine Y/N: ')
                        ->ask();

                    if ($ris_others_img->fetch() == "Y") {

                        $imgPr = "";
                        while (empty($imgPr)) {

                            $imgPr = $menu->askText()
                                ->setPromptText('Inserisci link altra immagine: ')
                                ->ask();

                            if (!empty($imgPr))
                                $immagini[] = ['src' => $imgPr->fetch()];
                        }
                    }
                }
                $data = [
                    'name' => $nomePr->fetch(),
                    'type' => 'variable',
                    'regular_price' => $prezzoPrFormat,
                    'description' => $desLPr->fetch(),
                    'short_description' => $desCPr->fetch(),
                    'manage_stock' => true,
                    'stock_quantity' => $qtPr->fetch(),
                    'sku' => $skuPr->fetch(),
                    'categories' => [
                        [
                            'id' => $catPr->fetch()
                        ]
                    ],
                    'images' => $immagini,
                    'attributes' =>
                    [
                        [
                            'id' => $attPr->fetch(),
                            'visible' => true,
                            'variation' => true,
                            'options' => [
                                $terminePr->fetch()
                            ]
                        ]
                    ]
                ];

                //echo print_r($data,true);
                $resVar = $woocommerce->post('products', $data);
                if (empty($resVar->id)) {
                    ascii_message("Errore generico durante l'inserimento. (1)", "white", "red");
                } else if (!empty($resVar->id)) {
                    ascii_message("Prodotto inserito con successo", "white", "green");
                } else {
                    ascii_message("Errore generico durante l'inserimento. (2)", "white", "red");
                }
            });
    })
    ->addSubMenu('Cerca prodotto tramite parola chiave', function (CliMenuBuilder $b) {

        $b->setTitle('Cerca > Options')
            ->addItem('Cerca prodotto tramite parola chiave', function (CliMenu $menu) {
                global $woocommerce;
                $key = $menu->askText()
                    ->setPromptText('Inserisci parola chiave:')
                    ->ask();
                $ris = ($woocommerce->get('products', ['search' => $key->fetch()]));
                $arrRis = array();
                foreach ($ris as $term) {

                    $arrRis[] = "Id: " . $term->id . " Nome: " . $term->name . " Quantita: " . $term->stock_quantity;
                }
                if (!empty($arrRis)) {
                    echo (implode("\n", $arrRis));
                    $idProd = $menu->askText()
                        ->setPromptText('Risultato della ricerca con termine: "' . $key->fetch() . '". Inserisci id prodotto')
                        ->ask();

                    if (!empty($idProd->fetch())) {

                        $newQt = $menu->askText()
                            ->setPromptText('Inserisci nuova quantita: ')
                            ->setValidationFailedText('Campo obbligatorio')
                            ->ask();

                        $data = [
                            'stock_quantity' => $newQt->fetch()
                        ];

                        $insertProduct = $woocommerce->put('products/' . $idProd->fetch(), $data);
                        if ($newQt->fetch() == $insertProduct->stock_quantity) {

                            ascii_message("Quantità modificata con successo", "white", "green");
                        } else {

                            ascii_message("Errore nella modifica, riprovare", "white", "red");
                        }
                    }
                } else {

                    ascii_message("Nessun risultato trovato", "white", "red");
                }
            });
    })
    ->addLineBreak('-')
    ->setPadding(2, 2)
    ->setMarginAuto()
    ->build();


$menu->open();
