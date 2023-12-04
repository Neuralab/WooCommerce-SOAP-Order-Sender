<?php
/**
 * Plugin Name: WooCommerce SOAP Order Sender
 * Description: Sends WooCommerce order data to an external SOAP service.
 * Version: 1.0
 * Author: Neuralab
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Hook into WooCommerce order status changed action
add_action( 'woocommerce_order_status_changed', 'send_order_to_soap_service', 10, 3 );

/**
 * Send order data to SOAP service
 *
 * @param int $order_id Order ID.
 * @param string $old_status Old status.
 * @param string $new_status New status.
 */
function send_order_to_soap_service( $order_id, $old_status, $new_status ) {
    if ( 'completed' === $new_status ) { // Change this to the status you want
        // Load the order
        $order = wc_get_order( $order_id );

        if ( ! $order ) {
            return;
        }

        // Prepare data for SOAP request
        $order_data = prepare_order_data_for_soap( $order );

        // Send the SOAP request
        send_soap_request( $order_data );
    }
}

/**
 * Prepare order data for SOAP request
 *
 * @param WC_Order $order WooCommerce Order object.
 * @return array
 */
function prepare_order_data_for_soap( $order ) {
    $nalogZaglavlje = array(
        'vanjskiIdentifikator' => $order->get_order_number(),
        'OIB'                 => /* OIB kupca, ako je dostupan */,
        'imeKupcaNaplata'     => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
        'adresaNaplata'       => $order->get_billing_address_1(),
        // Slično popunite ostale podatke za naplatu i dostavu
        'ukupniIznos'         => $order->get_total(),
        'nacinPlacanja'       => /* Kodirani način plaćanja, prilagodite vašem sustavu */
        // Dodajte ostale potrebne podatke
    );

    $nalogStavke = array();
    foreach ( $order->get_items() as $item_id => $item ) {
        $product = $item->get_product();
        $nalogStavka = array(
            'sifraProizvoda'    => $product->get_sku(),
            'tipProizvoda'      => /* U (usluga) ili A (artikl), ovisno o proizvodu */,
            'kolicina'          => $item->get_quantity(),
            'maloprodajnaCijena'=> $item->get_subtotal()
        );
        $nalogStavke[] = $nalogStavka;
    }

    return array(
        'NalogZaglavlje' => $nalogZaglavlje,
        'NalogStavke'    => $nalogStavke
    );
}

/**
 * Send SOAP request with order data
 *
 * @param array $order_data Formatted order data.
 */
function send_soap_request( $order_data ) {
    try {
        $client = new SoapClient( 'URL_OF_YOUR_SOAP_WSDL' ); // Replace with your WSDL URL

        // Format the SOAP request payload
        $soap_request = /* ... */;

        // Send the request
        $response = $client->posaljiNalog( $soap_request );

        // Handle the response
        // ...

    } catch ( Exception $e ) {
        // Handle exceptions
        error_log( 'SOAP Request failed: ' . $e->getMessage() );
    }
}

// Further functions and code as needed...
