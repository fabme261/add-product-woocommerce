<?php


require 'vendor/autoload.php';

use Automattic\WooCommerce\Client;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


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
//var_dump($woocommerce->get('products/attributes'));
//file_put_contents('prova.json', json_encode($woocommerce->get('products')));

$listCategorie = $woocommerce->get('products/categories');
$esclusi = array('slug', 'parent', 'description', 'display', 'image', 'menu_order', 'count', '_links', 'collection');
$rows = array();
foreach ($listCategorie as $value) {
    $temporaneo = array();
    foreach ($value as $key => $data) {
        //echo $key ."\n";
        if (!in_array($key, $esclusi)) {
            //echo("ok");
            array_push($temporaneo, $data);
        }
    }
    array_push($rows, $temporaneo);

    //array_push($rows,$value);

}
$liAttr = $woocommerce->get('products/attributes');
$esclusiA = array('slug', 'type', 'order_by', 'has_archives', '_links');
$rowsA = array();
foreach ($liAttr as $value) {
    $temporaneoA = array();
    foreach ($value as $key => $data) {
        //echo $key ."\n";
        if (!in_array($key, $esclusi)) {
            //echo("ok");
            array_push($temporaneoA, $data);
        }
    }
    array_push($rowsA, $temporaneoA);
}

echo ("1. Inserisci categoria \n");
echo ("2. Inserisci attributo \n");
echo ("3. Inserisci prodotto singolo \n");
echo ("4. Inserisci prodotto  variabile \n");
echo ("5. Modifica quantità prodotto tramite id \n \n");


$ask = readline("Inserisci il codice relativo alla voce del menu: ");

if ($ask == "") {
    echo ("Fai una scelta!! \n");
} elseif ($ask == 1) {
    echo ("hai scelto di inserire la categoria \n");
    $nomeCategoria = readline("Inserisci il nome della categoria: ");
    $imgCt = readline("Inserisci link immagine--opzionale: ");
    $data = [
        'name' => $nomeCategoria,
        'image' => [
            'src' => $imgCt
        ]
    ];

    $woocommerce->post('products/categories', $data);
} elseif ($ask == 2) {
    echo ("hai scelto di inserire l attributo \n");
    $nomeAttr = readline("Inserisci il nome: ");
    $slug = readline("Inserisci slug: ");
    $data = [
        'name' => $nomeAttr,
        'slug' => $slug,
        'type' => 'select',
        'order_by' => 'menu_order',
        'has_archives' => true
    ];

    $woocommerce->post('products/attributes', $data);
}
//------>>>>PRODOTTO SINGOLO<<<<------
elseif ($ask == 3) {
    echo ("hai scelto di inserire il prodotto singolo \n");

    $nome = "";
    while (empty($nome)) {

        $nome = readline("Inserisci il nome del prodotto: \n");
        if (empty($nome))
            echo ">>>>Nome campo obbligatorio!<<<<\n";
    }

    $price = readline("Inserisci il prezzo con il punto: \n");
    $descrizioneln = readline("Inserisci una descrizione lunga: ");
    $descrizionebr = readline("Inserisci una descrizione breve: ");

    $qt = readline("Inserisci quantita: ");
    $sku = "";
    while (empty($sku)) {

        $sku = readline("Inserisci codice sku: \n");
        if (empty($sku))
            echo ">>>>Sku campo obbligatorio!<<<<\n";
    }
    foreach ($rows as $keyR => $row) {

        echo $row[0] . ") " . $row[1] . "\n";
    }
    $categoriaa = readline("Inserisci un id categoria tra le elencate: ");
    foreach ($rowsA as $keyR => $row) {

        echo $row[0] . ") " . $row[1] . "\n";
    }


//echo $rowsA[$rispID];


    $att = readline("Inserisci id attributo: ");
    //print_r($rows);
    $liTerms = $woocommerce->get('products/attributes/' . $att . '/terms');
    $esclusiT = array('slug', 'description', 'menu_order', 'count', '_links');
    $rowsT = array();
    foreach ($liTerms as $value) {
        $temporaneoT = array();
        foreach ($value as $key => $data) {
            //echo $key ."\n";
            if (!in_array($key, $esclusiT)) {
                //echo("ok");
                array_push($temporaneoT, $data);
            }
        }
        array_push($rowsT, $temporaneoT);
    }

    foreach ($rowsT as $keyR => $row) {

        echo $row[0] . ") " . $row[1] . "\n";
    }
    $termine = readline("Inserisci termine dell'attributo: ");
    $immagini = array();
    /*
    $immagini=[
        [
            'src' => $img
        ],
        [
            'src' => ""
        ]
        ];
    */
    $img = "";
    while (empty($img)) {

        $img = readline("Inserisci link immagine: \n");
        if (empty($img))
            echo ">>>>Immagine campo obbligatorio!<<<<\n";
        else
            $immagini[] = ['src' => $img];
    }

    $ris_others_img = "Y";
    while ($ris_others_img == "Y") {

        $ris_others_img = readline("Vuoi inserire un altra immagine Y/N: ");

        if ($ris_others_img == "Y") {

            $img = "";
            while (empty($img)) {

                $img = readline("Inserisci link immagine: \n");
                if (empty($img))
                    echo ">>>>Immagine campo obbligatorio!<<<<\n";
                else
                    $immagini[] = ['src' => $img];
            }
        }
    }

    $data = [
        'name' => $nome,
        'type' => 'simple',
        'regular_price' => $price,
        'description' => $descrizioneln,
        'short_description' => $descrizionebr,
        'manage_stock' => true,
        'stock_quantity' => $qt,
        'sku' => $sku,
        'categories' => [
            [
                'id' => $categoriaa
            ]
        ],
        'images' => $immagini,
        'attributes' =>
        [
            [
                'id' => $att,
                'visible' => true,
                'variation' => true,
                'options' => [
                    $termine
                ]
            ]
        ]
    ];

    $woocommerce->post('products', $data);
} elseif ($ask == 4) {
    echo ("Hai scelto di inserire un prodotto variabile \n");

    
    //print_r($woocommerce->get('products/attributes'));}
    
   
    $nomeVar=readline("Inserisci il nome del prodotto: \n");
    $descrizionelnVar=readline("Inserisci una descrizione lunga: ");
    $descrizionebrVar=readline("Inserisci una descrizione breve: ");
    foreach($rows as $keyR => $row) {

        echo $row[0].") ".$row[1]."\n";
    }
    
    $categoriaVar=readline("Inserisci un id categoria tra le elencate: ");
    $imgVar=readline("Inserisci link immagine: ");
    $imgVar1=readline("Inserisci link immagine2: ");
    //$imgVar2=readline("Inserisci link immagine3: ");
    //$imgVar3=readline("Inserisci link immagine4: ");
    
    foreach($rows as $keyR => $row) {

            echo $row[0].") ".$row[1]."\n";
        }
    $attr=readline("Inserisci un id attributo tra quelli elencati: ");
    $priceVar=readline("Inserisci il prezzo con il punto: \n");
    $skuVar=readline("Inserisci codice sku: ");
    //print_r($rows);
    $data = [
        'name' => $nomeVar,
        'type' => 'variable',
        'description' => $descrizionelnVar,
        'short_description' => $descrizionebrVar,
        'categories' => [
            [
                'id' => $categoriaVar
            ],
            [
                'id' => $categoriaVar
            ]
        ],
        'images' => [
            [
                'src' => $imgVar
            ],
            [
                'src' => $imgVar1
            ]
        ],
        'attributes' => [
            [
                'id' => $attr,
                'position' => 0,
                'visible' => false,
                'variation' => true,
                'options' => [
                    'Black',
                    'Green'
                ]
            ],
            [
                'name' => 'Size',
                'position' => 0,
                'visible' => true,
                'variation' => true,
                'options' => [
                    'S',
                    'M'
                ]
            ]
        ],
        'default_attributes' => [
            [
                'id' => 6,
                'option' => 'Black'
            ],
            [
                'name' => 'Size',
                'option' => 'S'
            ]
        ]
    ];
    
    $woocommerce->post('products', $data);
}
else{
    echo ("Scelta non valida!! \n");
}
