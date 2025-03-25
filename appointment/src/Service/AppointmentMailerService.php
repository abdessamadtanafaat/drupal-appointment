<?php

namespace Drupal\appointment\Service;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Handles appointment confirmation emails.
 */
class AppointmentMailerService {

  use StringTranslationTrait;

  /**
   * The mail manager service.
   *
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected $mailManager;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The logger service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  protected $dateFormatter;

  /**
   * Constructs a new AppointmentMailer.
   *
   * @param \Drupal\Core\Mail\MailManagerInterface $mail_manager
   *   The mail manager service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory service.
   */
  public function __construct(
    MailManagerInterface $mail_manager,
    RendererInterface $renderer,
    LoggerChannelFactoryInterface $logger_factory,
    DateFormatterInterface $date_formatter

  ) {
    $this->mailManager = $mail_manager;
    $this->renderer = $renderer;
    $this->logger = $logger_factory->get('appointment');
    $this->dateFormatter = $date_formatter;

  }

  /**
   * Sends an appointment confirmation email.
   *
   * @param array $appointment
   *   The appointment data.
   * @param int $appointment_id
   *   The appointment ID.
   *
   * @return bool
   *   TRUE if email was sent successfully, FALSE otherwise.
   */
  public function sendConfirmationEmail(array $appointment, $appointment_id) {
    \Drupal::logger('appointment')->debug('Preparing confirmation email', [
      'appointment_id' => $appointment_id,
      'recipient' => $appointment['email']
    ]);

    $mailManager = \Drupal::service('plugin.manager.mail');
    $langcode = \Drupal::currentUser()->getPreferredLangcode();

    try {
      // Format dates
      $start_date = \Drupal::service('date.formatter')->format(strtotime($appointment['start_date']), 'custom', 'F j, Y g:i a');
      $end_date = \Drupal::service('date.formatter')->format(strtotime($appointment['end_date']), 'custom', 'g:i a');

      \Drupal::logger('appointment')->debug('Email date formatting complete', [
        'start_date' => $start_date,
        'end_date' => $end_date
      ]);

      $params = [
        'subject' => t('Your appointment confirmation'),
        'body' => [
          '#theme' => 'appointment_confirmation',
          '#appointment' => $appointment,
          '#appointment_id' => $appointment_id,
          '#start_date' => $start_date,
          '#end_date' => $end_date,
          '#advisor_name' => $appointment['advisor'],
          '#agency_name' => $appointment['agency'],
          '#appointment_type' => $appointment['appointment_type_name'],
        ],
      ];

      $to = $appointment['email'];

      \Drupal::logger('appointment')->debug('Attempting to send email', [
        'to' => $to,
        'params' => $params
      ]);

      $result = $mailManager->mail(
        'appointment',
        'confirmation',
        $to,
        $langcode,
        $params,
        NULL,
        TRUE
      );

      if ($result['result'] !== TRUE) {
        \Drupal::logger('appointment')->error('Email sending failed', [
          'error' => $result['message'] ?? 'Unknown error',
          'to' => $to,
          'appointment_id' => $appointment_id
        ]);
        return FALSE;
      }

      \Drupal::logger('appointment')->notice('Email sent successfully', [
        'to' => $to,
        'message_id' => $result['message_id'] ?? 'unknown'
      ]);

      return TRUE;

    } catch (\Exception $e) {
      \Drupal::logger('appointment')->error('Email sending exception', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
        'appointment_id' => $appointment_id
      ]);
      return FALSE;
    }
  }

  /**
   * Sends an appointment cancellation email.
   *
   * @param array $appointment
   *   The appointment data.
   * @param int $appointment_id
   *   The appointment ID.
   *
   * @return bool
   *   TRUE if email was sent successfully, FALSE otherwise.
   */
  public function sendCancellationEmail(array $appointment, int $appointment_id): bool {
    \Drupal::logger('appointment')->debug('Preparing cancellation email', [
      'appointment_id' => $appointment_id,
      'recipient' => $appointment['email']
    ]);

    $mailManager = \Drupal::service('plugin.manager.mail');
    $langcode = \Drupal::currentUser()->getPreferredLangcode();

    try {
      // Format dates
      $start_date = $this->dateFormatter->format(strtotime($appointment['start_date']), 'custom', 'F j, Y g:i a');
      $end_date = $this->dateFormatter->format(strtotime($appointment['end_date']), 'custom', 'g:i a');

      \Drupal::logger('appointment')->debug('Cancellation email date formatting complete', [
        'start_date' => $start_date,
        'end_date' => $end_date
      ]);

      $params = [
        'subject' => t('Your appointment cancellation confirmation'),
        'body' => [
          '#theme' => 'appointment_cancellation',
          '#appointment' => $appointment,
          '#appointment_id' => $appointment_id,
          '#start_date' => $start_date,
          '#end_date' => $end_date,
          '#advisor_name' => $appointment['advisor'],
          '#agency_name' => $appointment['agency'],
          '#appointment_type' => $appointment['appointment_type_name'],
        ],
      ];

      $to = $appointment['email'];

      \Drupal::logger('appointment')->debug('Attempting to send cancellation email', [
        'to' => $to,
        'params' => $params
      ]);

      $result = $mailManager->mail(
        'appointment',
        'cancellation',
        $to,
        $langcode,
        $params,
        NULL,
        TRUE
      );

      if ($result['result'] !== TRUE) {
        \Drupal::logger('appointment')->error('Cancellation email sending failed', [
          'error' => $result['message'] ?? 'Unknown error',
          'to' => $to,
          'appointment_id' => $appointment_id
        ]);
        return FALSE;
      }

      \Drupal::logger('appointment')->notice('Cancellation email sent successfully', [
        'to' => $to,
        'message_id' => $result['message_id'] ?? 'unknown'
      ]);

      return TRUE;

    } catch (\Exception $e) {
      \Drupal::logger('appointment')->error('Cancellation email sending exception', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
        'appointment_id' => $appointment_id
      ]);
      return FALSE;
    }
  }

}
