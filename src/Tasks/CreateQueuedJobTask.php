<?php

namespace Symbiote\QueuedJobs\Tasks;

use SilverStripe\Core\ClassInfo;
use SilverStripe\Dev\BuildTask;
use Symbiote\QueuedJobs\Services\AbstractQueuedJob;
use Symbiote\QueuedJobs\Services\QueuedJobService;

/**
 * A task that can be used to create a queued job.
 *
 * Useful to hook a queued job in to place that needs to exist if it doesn't already.
 *
 * If no name is given, it creates a demo dummy job to help test that things
 * are set up and working
 *
 * @author Marcus Nyeholt <marcus@symbiote.com.au>
 * @license BSD http://silverstripe.org/bsd-license/
 */
class CreateQueuedJobTask extends BuildTask
{
    /**
     * {@inheritDoc}
     * @var string
     */
    private static $segment = 'CreateQueuedJobTask';

    /**
     * @return string
     */
    public function getDescription()
    {
        return _t(
            __CLASS__ . '.Description',
            'A task used to create a queued job. Pass the queued job class name as the "name" parameter, pass an optional "start" parameter (parseable by strtotime) to set a start time for the job.'
        );
    }

    /**
     * @param HTTPRequest $request
     */
    public function run($request)
    {
        if (isset($request['name']) && ClassInfo::exists($request['name'])) {
            $clz = $request['name'];
            $job = new $clz;
        } else {
            $job = new DummyQueuedJob(mt_rand(10, 100));
        }

        if (isset($request['start'])) {
            $start = strtotime($request['start']);
            $now = time();
            if ($start >= $now) {
                $friendlyStart = date('Y-m-d H:i:s', $start);
                echo 'Job queued to start at: <b>' . $friendlyStart . '</b>';
                singleton(QueuedJobService::class)->queueJob($job, $start);
            } else {
                echo "'start' parameter must be a date/time in the future, parseable with strtotime";
            }
        } else {
            echo "Job Queued";
            singleton(QueuedJobService::class)->queueJob($job);
        }
    }
}
