<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Filesystem\Filesystem;


#[AsCommand(
    name: 'app:get-twitter-posts',
    description: 'Get recent twitter posts for local storage',
)]
class GetTwitterPostsCommand extends Command
{
    private HttpClientInterface $http;
    private Filesystem $filesystem;

    const USERS = [
        'NASA',
        'SpaceX',
        'BoeingSpace'
    ];

    const TWEETS_DIR = 'files/';

    const URL = 'https://api.twitter.com/2';
    const URL_RECENT_TWEETS = '/tweets/search/recent?query=from:';
    const BEARER_TOKEN = 'AAAAAAAAAAAAAAAAAAAAAG%2B9igEAAAAASkVqhfz%2F4sOKqMD9Wadhh%2F93ReE%3DRu3xdQVtZN7M0ucbY60g8loLbOxxDggXplInZnSovvJ8t8E2ln';

    protected function configure(): void
    {
        $this->http = HttpClient::create([
            'auth_bearer' => self::BEARER_TOKEN
        ]);

        $this->filesystem = new Filesystem();

        if(!$this->filesystem->exists(self::TWEETS_DIR)) {
            $this->filesystem->mkdir(self::TWEETS_DIR);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->write('Requesting JSON files..');

        foreach (self::USERS as $user) {
            $recentTweets = $this
                ->http
                ->request('GET', self::URL . self::URL_RECENT_TWEETS . $user);

            if ($recentTweets->getStatusCode() == 200) {
                $this
                    ->filesystem
                    ->dumpFile(self::TWEETS_DIR . $user . '.json',
                        $recentTweets->getContent());
            }
        }

        $io->writeln(' DONE');
        $io->success('JSON files downloaded.');

        return Command::SUCCESS;
    }
}
