<?php

/*
 * This file is part of the hyn/multi-tenant package.
 *
 * (c) Daniël Klabbers <daniel@klabbers.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see https://github.com/hyn/multi-tenant
 * @see https://hyn.me
 * @see https://patreon.com/tenancy
 */

namespace Hyn\Tenancy\Tests\Filesystem;

use Hyn\Tenancy\Tests\Test;
use Hyn\Tenancy\Website\Directory;
use Illuminate\Contracts\Foundation\Application;

class LoadsConfigsTest extends Test
{
    /**
     * @var Directory
     */
    protected $directory;

    protected function duringSetUp(Application $app)
    {
        $this->setUpHostnames(true);
        $this->setUpWebsites(true, true);

        $this->directory = $app->make(Directory::class);
        $this->directory->setWebsite($this->website);
    }

    /**
     * @test
     */
    public function reads_additional_config()
    {
        // Directory should now exists, let's write the config folder.
        $this->assertTrue($this->directory->makeDirectory('config'));

        // Write a testing config.
        $this->assertTrue($this->directory->put('config' . DIRECTORY_SEPARATOR . 'test.php', <<<EOM
<?php

return ['foo' => 'bar'];
EOM
));

        $this->assertTrue($this->directory->exists('config/test.php'));

        $this->activateTenant('local');

        $this->assertEquals('bar', config('test.foo'));
    }

    /**
     * @test
     */
    public function blocks_blacklisted_configs()
    {
        // Directory should now exists, let's write the config folder.
        $this->assertTrue($this->directory->makeDirectory('config'));

        // Write a testing config.
        $this->assertTrue($this->directory->put('config' . DIRECTORY_SEPARATOR . 'database.php', <<<EOM
<?php

return ['foo' => 'bar'];
EOM
        ));

        $this->assertTrue($this->directory->exists('config/database.php'));

        $this->activateTenant('local');

        $this->assertNull(config('database.foo'));
    }
}
