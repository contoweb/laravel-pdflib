<?php

namespace Contoweb\Pdflib\Tests;

use Contoweb\Pdflib\LaravelPdflibServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    /**
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            LaravelPdflibServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('filesystems.disks.local.root', __DIR__ . '/Data/Storage/Local');
        $app['config']->set('filesystems.disks.other', [
            'driver' => 'local',
            'root'   => __DIR__ . '/Data/Storage/Other',
        ]);

        $app['config']->set('pdf.exports.disk', 'local');
        $app['config']->set('pdf.exports.path', '');
        $app['config']->set('pdf.fonts.disk', 'local');
        $app['config']->set('pdf.fonts.path', 'fonts');
        $app['config']->set('pdf.templates.disk', 'local');
        $app['config']->set('pdf.templates.path', 'templates');
    }
}
