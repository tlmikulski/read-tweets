<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;
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

    private $users;
    private $tweetsDir;
    private $bearerToken;

    const URL = 'https://api.twitter.com/2';
    const URL_RECENT_TWEETS = '/tweets/search/recent?query=from:';

    public function __construct($users, $tweetsDir, $bearerToken)
    {
        $this->users = $users;
        $this->tweetsDir = $tweetsDir;
        $this->bearerToken = $bearerToken;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->http = HttpClient::create([
            'auth_bearer' => $this->bearerToken
        ]);

        $this->filesystem = new Filesystem();

        if(!$this->filesystem->exists($this->tweetsDir)) {
            $this->filesystem->mkdir($this->tweetsDir);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->write('Requesting JSON files..');

        foreach ($this->users as $user) {
            $recentTweets = $this
                ->http
                ->request('GET',
                    self::URL . self::URL_RECENT_TWEETS . $user);

            if ($recentTweets->getStatusCode() == Response::HTTP_OK) {
                $this
                    ->filesystem
                    ->dumpFile($this->tweetsDir . $user . '.json',
                        $recentTweets->getContent());
            } else {
                $io->error(
                    vsprintf(
                        'Cannot download JSON, HTTP status code: %s',
                        [ $recentTweets->getStatusCode() ]));

                return Command::FAILURE;
            }
        }

        $io->writeln(' DONE');
        $io->success('JSON files downloaded.');

        return Command::SUCCESS;
    }
}
