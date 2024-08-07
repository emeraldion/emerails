<?php
/**
 *                                   _ __
 *   ___  ____ ___  ___  _________ _(_) /____
 *  / _ \/ __ `__ \/ _ \/ ___/ __ `/ / / ___/
 * /  __/ / / / / /  __/ /  / /_/ / / (__  )
 * \___/_/ /_/ /_/\___/_/   \__,_/_/_/____/
 *
 * (c) Claudio Procida 2008-2024
 *
 * @format
 */

require_once __DIR__ . '/../base_test.php';

use Emeraldion\EmeRails\Helpers\ReadingTime;

class ReadingTimeTest extends UnitTest
{
    const LOREM_IPSUM = <<<EOT
    Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec a diam lectus. Sed
    sit amet ipsum mauris. Maecenas congue ligula ac quam viverra nec consectetur ante
    hendrerit. Donec et mollis dolor. Praesent et diam eget libero egestas mattis sit
    amet vitae augue. Nam tincidunt congue enim, ut porta lorem lacinia consectetur.
    Donec ut libero sed arcu vehicula ultricies a non tortor. Lorem ipsum dolor sit
    amet, consectetur adipiscing elit. Aenean ut gravida lorem. Ut turpis felis,
    pulvinar a semper sed, adipiscing id dolor. Pellentesque auctor nisi id magna
    consequat sagittis. Curabitur dapibus enim sit amet elit pharetra tincidunt
    feugiat nisl imperdiet. Ut convallis libero in urna ultrices accumsan. Donec sed
    odio eros. Donec viverra mi quis quam pulvinar at malesuada arcu rhoncus. Cum
    sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.
    In rutrum accumsan ultricies. Mauris vitae nisi at sem facilisis semper ac in est.

    Vivamus fermentum semper porta. Nunc diam velit, adipiscing ut tristique vitae, sagittis
    vel odio. Maecenas convallis ullamcorper ultricies. Curabitur ornare, ligula semper
    consectetur sagittis, nisi diam iaculis velit, id fringilla sem nunc vel mi. Nam dictum,
    odio nec pretium volutpat, arcu ante placerat erat, non tristique elit urna et turpis.
    Quisque mi metus, ornare sit amet fermentum et, tincidunt et orci. Fusce eget orci a orci
    congue vestibulum. Ut dolor diam, elementum et vestibulum eu, porttitor vel elit.
    Curabitur venenatis pulvinar tellus gravida ornare. Sed et erat faucibus nunc euismod
    ultricies ut id justo. Nullam cursus suscipit nisi, et ultrices justo sodales nec. Fusce
    venenatis facilisis lectus ac semper. Aliquam at massa ipsum. Quisque bibendum purus
    convallis nulla ultrices ultricies. Nullam aliquam, mi eu aliquam tincidunt, purus velit
    laoreet tortor, viverra pretium nisi quam vitae mi. Fusce vel volutpat elit. Nam sagittis
    nisi dui.

    Suspendisse lectus leo, consectetur in tempor sit amet, placerat quis neque. Etiam luctus
    porttitor lorem, sed suscipit est rutrum non. Curabitur lobortis nisl a enim congue
    semper. Aenean commodo ultrices imperdiet. Vestibulum ut justo vel sapien venenatis
    tincidunt. Phasellus eget dolor sit amet ipsum dapibus condimentum vitae quis lectus.
    Aliquam ut massa in turpis dapibus convallis. Praesent elit lacus, vestibulum at
    malesuada et, ornare et est. Ut augue nunc, sodales ut euismod non, adipiscing vitae
    orci. Mauris ut placerat justo. Mauris in ultricies enim. Quisque nec est eleifend nulla
    ultrices egestas quis ut quam. Donec sollicitudin lectus a mauris pulvinar id aliquam
    urna cursus. Cras quis ligula sem, vel elementum mi. Phasellus non ullamcorper urna.

    Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos
    himenaeos. In euismod ultrices facilisis. Vestibulum porta sapien adipiscing augue congue
    id pretium lectus molestie. Proin quis dictum nisl. Morbi id quam sapien, sed vestibulum
    sem. Duis elementum rutrum mauris sed convallis. Proin vestibulum magna mi. Aenean
    tristique hendrerit magna, ac facilisis nulla hendrerit ut. Sed non tortor sodales quam
    auctor elementum. Donec hendrerit nunc eget elit pharetra pulvinar. Suspendisse id tempus
    tortor. Aenean luctus, elit commodo laoreet commodo, justo nisi consequat massa, sed
    vulputate quam urna quis eros. Donec vel.

    Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec a diam lectus. Sed
    sit amet ipsum mauris. Maecenas congue ligula ac quam viverra nec consectetur ante
    hendrerit. Donec et mollis dolor. Praesent et diam eget libero egestas mattis sit
    amet vitae augue. Nam tincidunt congue enim, ut porta lorem lacinia consectetur.
    Donec ut libero sed arcu vehicula ultricies a non tortor. Lorem ipsum dolor sit
    amet, consectetur adipiscing elit. Aenean ut gravida lorem. Ut turpis felis,
    pulvinar a semper sed, adipiscing id dolor. Pellentesque auctor nisi id magna
    consequat sagittis. Curabitur dapibus enim sit amet elit pharetra tincidunt
    feugiat nisl imperdiet. Ut convallis libero in urna ultrices accumsan. Donec sed
    odio eros. Donec viverra mi quis quam pulvinar at malesuada arcu rhoncus. Cum
    sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.
    In rutrum accumsan ultricies. Mauris vitae nisi at sem facilisis semper ac in est.

    Vivamus fermentum semper porta. Nunc diam velit, adipiscing ut tristique vitae, sagittis
    vel odio. Maecenas convallis ullamcorper ultricies. Curabitur ornare, ligula semper
    consectetur sagittis, nisi diam iaculis velit, id fringilla sem nunc vel mi. Nam dictum,
    odio nec pretium volutpat, arcu ante placerat erat, non tristique elit urna et turpis.
    Quisque mi metus, ornare sit amet fermentum et, tincidunt et orci. Fusce eget orci a orci
    congue vestibulum. Ut dolor diam, elementum et vestibulum eu, porttitor vel elit.
    Curabitur venenatis pulvinar tellus gravida ornare. Sed et erat faucibus nunc euismod
    ultricies ut id justo. Nullam cursus suscipit nisi, et ultrices justo sodales nec. Fusce
    venenatis facilisis lectus ac semper. Aliquam at massa ipsum. Quisque bibendum purus
    convallis nulla ultrices ultricies. Nullam aliquam, mi eu aliquam tincidunt, purus velit
    laoreet tortor, viverra pretium nisi quam vitae mi. Fusce vel volutpat elit. Nam sagittis
    nisi dui.

    Suspendisse lectus leo, consectetur in tempor sit amet, placerat quis neque. Etiam luctus
    porttitor lorem, sed suscipit est rutrum non. Curabitur lobortis nisl a enim congue
    semper. Aenean commodo ultrices imperdiet. Vestibulum ut justo vel sapien venenatis
    tincidunt. Phasellus eget dolor sit amet ipsum dapibus condimentum vitae quis lectus.
    Aliquam ut massa in turpis dapibus convallis. Praesent elit lacus, vestibulum at
    malesuada et, ornare et est. Ut augue nunc, sodales ut euismod non, adipiscing vitae
    orci. Mauris ut placerat justo. Mauris in ultricies enim. Quisque nec est eleifend nulla
    ultrices egestas quis ut quam. Donec sollicitudin lectus a mauris pulvinar id aliquam
    urna cursus. Cras quis ligula sem, vel elementum mi. Phasellus non ullamcorper urna.

    Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos
    himenaeos. In euismod ultrices facilisis. Vestibulum porta sapien adipiscing augue congue
    id pretium lectus molestie. Proin quis dictum nisl. Morbi id quam sapien, sed vestibulum
    sem. Duis elementum rutrum mauris sed convallis. Proin vestibulum magna mi. Aenean
    tristique hendrerit magna, ac facilisis nulla hendrerit ut. Sed non tortor sodales quam
    auctor elementum. Donec hendrerit nunc eget elit pharetra pulvinar. Suspendisse id tempus
    tortor. Aenean luctus, elit commodo laoreet commodo, justo nisi consequat massa, sed
    vulputate quam urna quis eros. Donec vel.
    EOT;

    public function test()
    {
        $this->assertEquals(3, ReadingTime::minutes_for(self::LOREM_IPSUM));
    }
}
