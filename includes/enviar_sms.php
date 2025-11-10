<?php
/**
 * Función para enviar mensajes por WhatsApp o SMS.
 * - Si no hay API real configurada, simplemente registra o muestra el mensaje.
 * - Si configurás Twilio o una API real, descomentá el bloque correspondiente.
 */

function enviarMensajePaciente($telefono, $mensaje) {
    // Limpieza del número: elimina espacios, guiones, paréntesis
    $telefono = preg_replace('/[^0-9+]/', '', $telefono);

    // Si querés simular envíos:
    file_put_contents(__DIR__ . '/log_sms.txt', date('Y-m-d H:i:s') . " | $telefono | $mensaje\n", FILE_APPEND);
    return true;

    /* === OPCIONAL: Envío real con Twilio ===
    require_once 'vendor/autoload.php';
    use Twilio\Rest\Client;

    $sid = 'TU_TWILIO_SID';
    $token = 'TU_TWILIO_TOKEN';
    $from = 'whatsapp:+14155238886'; // o número SMS

    $twilio = new Client($sid, $token);
    $twilio->messages->create(
        "whatsapp:$telefono", // o simplemente $telefono si es SMS
        ["from" => $from, "body" => $mensaje]
    );
    */
}
