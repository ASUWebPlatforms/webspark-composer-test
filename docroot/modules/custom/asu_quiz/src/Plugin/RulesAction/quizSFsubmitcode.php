<?php


namespace Drupal\asu_quiz\Plugin\RulesAction;

use Drupal\rules\Core\RulesActionBase;

/**
 * Provides a 'Show message on your site' action.
 *
 * @RulesAction(
 *   id = "quizSFsubmitcode",
 *   label = @Translation("asuquiz Show message"),
 *   category = @Translation("asuquiz"),
 *   context_definitions = {
 *     "message" = @ContextDefinition("string",
 *       label = @Translation("Message"),
 *       description = @Translation("write your message"),
 *     ),
 *     "type" = @ContextDefinition("string",
 *       label = @Translation("Message type"),
 *       description = @Translation("Message type: status, warning or error "),
 *     ),
 *   }
 * )
 *
 */
class quizSFsubmitcode extends RulesActionBase
{

    /**
     * @param $name
     */
    protected function doExecute($message, $type)
    {
        \Drupal::messenger()->addMessage(t($message), $type);
    }

}
