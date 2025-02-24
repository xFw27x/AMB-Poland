<?php
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'vendor/autoload.php'; // Ścieżka do autoload.php Composera

use Brevo\Client\Configuration;
use Brevo\Client\ApiClient;
use Brevo\Client\Model\SendSmtpEmail;
use Brevo\Client\Api\TransactionalEmailsApi;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = strip_tags(trim($_POST["name"]));
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $message = trim($_POST["message"]);

    if (empty($name) || empty($message) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo "Proszę poprawnie wypełnić formularz i spróbować ponownie.";
        exit;
    }

    $recipientEmail = "fw27@vp.pl"; // Zmień na swój adres email
    $subject = "Nowa wiadomość z formularza kontaktowego AMB Poland";
    $apiKey = getenv('BREVO_API_KEY'); // Pobierz klucz API z zmiennej środowiskowej
    if (empty($apiKey)) {
        $apiKey = '8d6rKWHB7cv2CJZP'; // Tymczasowo, ale lepiej używać zmiennej środowiskowej
    }

    // Konfiguracja Brevo
    $config = Configuration::getDefaultConfiguration()->setApiKey('api-key', $apiKey);

    // Inicjalizacja API client
    $apiClient = new ApiClient();
    $apiClient->setConfig($config);

    // Inicjalizacja Transactional Emails API
    $transactionalEmailsApi = new TransactionalEmailsApi($apiClient);

    // Tworzenie wiadomości email
    $sendSmtpEmail = new SendSmtpEmail([
        'sender' => [
            'name' => $name,
            'email' => $email
        ],
        'to' => [
            [
                'email' => $recipientEmail,
                'name' => 'Odbiorca' // Możesz to zmienić
            ]
        ],
        'subject' => $subject,
        'textContent' => "Imię: $name\nEmail: $email\nWiadomość:\n$message"
    ]);

    try {
        $result = $transactionalEmailsApi->sendTransacEmail($sendSmtpEmail);
        http_response_code(200);
        echo "Dziękujemy! Twoja wiadomość została wysłana.";
    } catch (Exception $e) {
        http_response_code(500);
        echo "Ups! Wystąpił problem i nie udało się wysłać Twojej wiadomości. Spróbuj ponownie. Błąd: " . $e->getMessage();
    }
} else {
    http_response_code(403);
    echo "Wystąpił problem i nie udało się wysłać Twojej wiadomości. Spróbuj ponownie.";
}
?>
