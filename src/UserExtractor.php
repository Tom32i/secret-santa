<?php

/*
 * This file is part of the Slack Secret Santa project.
 *
 * (c) JoliCode <coucou@jolicode.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Joli\SlackSecretSanta;

use CL\Slack\Model\User;
use CL\Slack\Payload\PayloadInterface;
use CL\Slack\Payload\PayloadResponseInterface;
use CL\Slack\Payload\UsersListPayload;
use CL\Slack\Payload\UsersListPayloadResponse;
use CL\Slack\Transport\ApiClient;

class UserExtractor
{
    /** @var ApiClient */
    private $apiClient;

    public function __construct(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    /**
     * @return User[]
     */
    public function extractAll(string $token): array
    {
        $payload = new UsersListPayload();

        /** @var $response UsersListPayloadResponse */
        $response = $this->sendPayload($payload, $token);

        return array_filter($response->getUsers(), function (User $user) {
            return
                !$user->isBot()
                && !$user->isDeleted()
                && $user->getName() !== 'slackbot'
            ;
        });
    }

    private function sendPayload(PayloadInterface $payload, string $token): PayloadResponseInterface
    {
        $response = $this->apiClient->send($payload, $token);

        if (!$response->isOk()) {
            throw new \RuntimeException(
                sprintf('%s (%s)', $response->getErrorExplanation(), $response->getError())
            );
        }

        return $response;
    }
}
