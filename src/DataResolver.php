<?php

declare(strict_types=1);

namespace App;

use SensitiveParameter;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

readonly class DataResolver
{
    const string USER_AGENT = 'https://github.com/vuryss/everybody-codes-php by vuryss@gmail.com';
    const string VERSION = '6';

    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private string $projectDir,
        private HttpClientInterface $httpClient,
        #[SensitiveParameter]
        private string $sessionToken,
    ) {
    }

    public function getDataForEventAndQuest(int $year, int $day): QuestData
    {
        $cacheFile = sprintf('%s/inputs/%s/%s', $this->projectDir, $year, $day);

        if (!file_exists($cacheFile)) {
            $downloadedInput = $this->downloadForEventAndQuest($year, $day);

            if (!file_exists(dirname($cacheFile))) {
                mkdir(dirname($cacheFile), 0777, true);
            }

            if (isset($downloadedInput['answer3'])) {
                file_put_contents($cacheFile, json_encode($downloadedInput));
            }

            return new QuestData(
                input1: $downloadedInput['part1'],
                input2: $downloadedInput['part2'],
                input3: $downloadedInput['part3'],
                answer1: $downloadedInput['answer1'],
                answer2: $downloadedInput['answer2'],
                answer3: $downloadedInput['answer3'],
            );
        }

        $inputs = json_decode(file_get_contents($cacheFile), associative: true);

        return new QuestData(
            input1: $inputs['part1'],
            input2: $inputs['part2'],
            input3: $inputs['part3'],
            answer1: $inputs['answer1'],
            answer2: $inputs['answer2'],
            answer3: $inputs['answer3'],
        );
    }

    /**
     * @return array{
     *     part1: string,
     *     part2: string|null,
     *     part3: string|null,
     *     answer1: string|null,
     *     answer2: string|null,
     *     answer3: string|null
     * }
     */
    private function downloadForEventAndQuest(int $event, int $quest): array
    {
        echo 'Downloading data for ' . $event . ' quest ' . $quest . PHP_EOL;

        $seed = $this->resolveUserSeed();
        $questData = $this->getQuestData($event, $quest);
        $questInput = $this->getQuestInput($event, $quest, $seed);

        $part1input = $this->decode($questInput[1], $questData['key1']);

        if (null !== $questData['key2']) {
            $part2input = $this->decode($questInput[2], $questData['key2']);
        }

        if (null !== $questData['key3']) {
            $part3input = $this->decode($questInput[3], $questData['key3']);
        }

        return [
            'part1' => $part1input,
            'part2' => $part2input ?? null,
            'part3' => $part3input ?? null,
            'answer1' => $questData['answer1'],
            'answer2' => $questData['answer2'],
            'answer3' => $questData['answer3'],
        ];
    }

    private function resolveUserSeed(): string
    {
        $response = $this->httpClient->request(
            'GET',
            'https://everybody.codes/api/user/me',
            [
                'headers' => [
                    'cookie' => 'everybody-codes=' . $this->sessionToken,
                    'user-agent' => self::USER_AGENT,
                ],
            ]
        );

        $content = json_decode($response->getContent(), associative: true);

        return (string) $content['seed'];
    }

    /**
     * @return array{
     *     key1: string,
     *     key2: string|null,
     *     key3: string|null,
     *     answer1: string|null,
     *     answer2: string|null,
     *     answer3: string|null
     * }
     */
    private function getQuestData(int $event, int $quest): array
    {
        $response = $this->httpClient->request(
            'GET',
            sprintf('https://everybody.codes/api/event/%s/quest/%s', $event, $quest),
            [
                'headers' => [
                    'cookie' => 'everybody-codes=' . $this->sessionToken,
                    'user-agent' => self::USER_AGENT,
                ],
            ]
        );

        $response = json_decode($response->getContent(), associative: true);

        $key1 = $response['key1'];
        $key1[20] = '~';

        if (isset($response['key2'])) {
            $key2 = $response['key2'];
            $key2[20] = '~';
        }

        if (isset($response['key3'])) {
            $key3 = $response['key3'];
            $key3[20] = '~';
        }

        return [
            'key1' => $key1,
            'key2' => $key2 ?? null,
            'key3' => $key3 ?? null,
            'answer1' => $response['answer1'] ?? null,
            'answer2' => $response['answer2'] ?? null,
            'answer3' => $response['answer3'] ?? null,
        ];
    }

    private function getQuestInput(int $event, int $quest, string $seed): array
    {
        $response = $this->httpClient->request(
            'GET',
            sprintf('https://everybody-codes.b-cdn.net/assets/%s/%s/input/%s.json?v=%s', $event, $quest, $seed, self::VERSION),
            [
                'headers' => [
                    'cookie' => 'everybody-codes=' . $this->sessionToken,
                    'user-agent' => self::USER_AGENT,
                ],
            ]
        );

        return json_decode($response->getContent(), associative: true);
    }

    public function decode($string, string $encryptionKey): string
    {
        return openssl_decrypt(
            hex2bin($string),
            'AES-256-CBC',
            $encryptionKey,
            OPENSSL_RAW_DATA,
            substr($encryptionKey, 0, 16),
        );
    }
}
