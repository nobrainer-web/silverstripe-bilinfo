<?php


namespace NobrainerWeb\Bilinfo\Tasks;


use NobrainerWeb\Bilinfo\Listings\Listing;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Dev\BuildTask;
use SilverStripe\Dev\Debug;

class CleanupListingsTask extends BuildTask
{
    protected $title = 'Clean up sold cars (listings)';

    /**
     * Set a custom url segment (to follow dev/tasks/)
     *
     * @config
     * @var string
     */
    private static $segment = 'bi-cleanup-listings-task';

    protected $verbose = false;

    /**
     * Implement this method in the task subclass to
     * execute via the TaskRunner
     *
     * @param HTTPRequest $request
     */
    public function run($request)
    {
        $this->verbose = (bool)$request->getVar('verbose');

        if (!Listing::config()->get('enabled_automatic_cleanup')) {
            $this->log('Automatic cleanup is disabled');

            return;
        }


        $listings = Listing::get()->filterByCallback(static function ($obj) {
            return $obj->canBeAutomaticallyDeleted();
        });

        if (!$listings->exists()) {
            $this->log('No listings to delete');

            return;
        }

        foreach ($listings as $listing) {
            $title = $listing->getTitle();
            $this->log('Deleting ' . $title . '...');
            $listing->delete();
        }

        $this->log('Finished');
    }

    protected function log($msg)
    {
        if ($this->verbose) {
            Debug::message($msg, false);
        }
    }

}