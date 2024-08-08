<?php

declare(strict_types=1);

namespace App\Service;

use Pheanstalk\Pheanstalk;

class BeanstalkdService
{
    private Pheanstalk $pheanstalk;

    public function __construct(string $host, int $port = 11300)
    {
        $this->pheanstalk = Pheanstalk::create($host, $port);
    }

    public function getPheanstalk(): Pheanstalk
    {
        return $this->pheanstalk;
    }

}
