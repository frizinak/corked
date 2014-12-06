<?php
namespace Frizinak\CorkedTest\Definition;

use Frizinak\Corked\Definition\InstructionsDefinition;

class InstructionsDefinitionTest extends \PHPUnit_Framework_TestCase
{

    public function validInstructionsProvider()
    {
        $data = array();
        $data[] = array(
            'input' => array(
                "FROM scratch",
                "ADD precise-core-amd64.tar.gz /",
                "# a few minor docker-specific tweaks",
                "# see https://github.com/docker/docker/blob/master/contrib/mkimage/debootstrap",
                array(
                    "RUN echo '#!/bin/sh' > /usr/sbin/policy-rc.d ",
                    "&& echo 'exit 101' >> /usr/sbin/policy-rc.d ",
                    "&& chmod +x /usr/sbin/policy-rc.d",
                    " ",
                    "&& dpkg-divert --local --rename --add /sbin/initctl ",
                    "&& cp -a /usr/sbin/policy-rc.d /sbin/initctl ",
                    "&& sed -i 's/^exit.*/exit 0/' /sbin/initctl ",
                    " ",
                    "&& echo 'force-unsafe-io' > /etc/dpkg/dpkg.cfg.d/docker-apt-speedup ",
                    " ",
                    "&& echo 'DPkg::Post-Invoke { \"rm -f /var/cache/apt/archives/*.deb /var/cache/apt/archives/partial/*.deb /var/cache/apt/*.bin || true\"; };' > /etc/apt/apt.conf.d/docker-clean ",
                    "&& echo 'APT::Update::Post-Invoke { \"rm -f /var/cache/apt/archives/*.deb /var/cache/apt/archives/partial/*.deb /var/cache/apt/*.bin || true\"; };' >> /etc/apt/apt.conf.d/docker-clean ",
                    "&& echo 'Dir::Cache::pkgcache \"\"; Dir::Cache::srcpkgcache \"\";' >> /etc/apt/apt.conf.d/docker-clean ",
                    " ",
                    "&& echo 'Acquire::Languages \"none\";' > /etc/apt/apt.conf.d/docker-no-languages ",
                    " ",
                    "&& echo 'Acquire::GzipIndexes \"true\"; Acquire::CompressionTypes::Order:: \"gz\";' > /etc/apt/apt.conf.d/docker-gzip-indexes",
                ),
                "# delete all the apt list files since they're big and get stale quickly",
                "RUN rm - rf /var/lib / apt / lists/*",
                "# this forces \"apt-get update\" in dependent images, which is also good",
                "# enable the universe",
                "RUN sed -i 's/^#\\s*\\(deb.*universe\\)$/\1/g' /etc/apt/sources.list",
                "# upgrade packages for now, since the tarballs aren't updated frequently enough",
                "RUN apt-get update && apt-get dist-upgrade -y && rm -rf /var/lib/apt/lists/*",
                "# overwrite this with 'CMD []' in a dependent Dockerfile",
                "CMD [\"/bin/bash\"]",
            ),
            'assertion' => <<<'nowdoc'
FROM scratch
ADD precise-core-amd64.tar.gz /
# a few minor docker-specific tweaks
# see https://github.com/docker/docker/blob/master/contrib/mkimage/debootstrap
RUN echo '#!/bin/sh' > /usr/sbin/policy-rc.d  \
&& echo 'exit 101' >> /usr/sbin/policy-rc.d  \
&& chmod +x /usr/sbin/policy-rc.d \
  \
&& dpkg-divert --local --rename --add /sbin/initctl  \
&& cp -a /usr/sbin/policy-rc.d /sbin/initctl  \
&& sed -i 's/^exit.*/exit 0/' /sbin/initctl  \
  \
&& echo 'force-unsafe-io' > /etc/dpkg/dpkg.cfg.d/docker-apt-speedup  \
  \
&& echo 'DPkg::Post-Invoke { "rm -f /var/cache/apt/archives/*.deb /var/cache/apt/archives/partial/*.deb /var/cache/apt/*.bin || true"; };' > /etc/apt/apt.conf.d/docker-clean  \
&& echo 'APT::Update::Post-Invoke { "rm -f /var/cache/apt/archives/*.deb /var/cache/apt/archives/partial/*.deb /var/cache/apt/*.bin || true"; };' >> /etc/apt/apt.conf.d/docker-clean  \
&& echo 'Dir::Cache::pkgcache ""; Dir::Cache::srcpkgcache "";' >> /etc/apt/apt.conf.d/docker-clean  \
  \
&& echo 'Acquire::Languages "none";' > /etc/apt/apt.conf.d/docker-no-languages  \
  \
&& echo 'Acquire::GzipIndexes "true"; Acquire::CompressionTypes::Order:: "gz";' > /etc/apt/apt.conf.d/docker-gzip-indexes
# delete all the apt list files since they're big and get stale quickly
RUN rm - rf /var/lib / apt / lists/*
# this forces "apt-get update" in dependent images, which is also good
# enable the universe
RUN sed -i 's/^#\s*\(deb.*universe\)$//g' /etc/apt/sources.list
# upgrade packages for now, since the tarballs aren't updated frequently enough
RUN apt-get update && apt-get dist-upgrade -y && rm -rf /var/lib/apt/lists/*
# overwrite this with 'CMD []' in a dependent Dockerfile
CMD ["/bin/bash"]
nowdoc
        );

        $data[] = array(
            'input' => array(
                "FROM ubuntu:14.04",
                "RUN apt-get update",
                "RUN apt-get install php5 apache2 nginx",
            ),
            'assertion' => <<<'nowdoc'
FROM ubuntu:14.04
RUN apt-get update
RUN apt-get install php5 apache2 nginx
nowdoc
        );

        $data[] = array('FROM ubuntu:14.04', 'FROM ubuntu:14.04');
        $data[] = array(array(array()), '');

        return $data;
    }

    public function invalidInstructionsProvider()
    {
        $data[] = array(1);
        $data[] = array(array(1));
        $data[] = array(new \stdClass());
        $data[] = array(array(new \stdClass()));
        $data[] = array(array(array(array())));
        $data[] = array(array(array(new \stdClass())));
        $data[] = array(array('apt-get', array('ls -alh', array('too-deep'))));
        return $data;
    }

    /**
     * @dataProvider validInstructionsProvider
     */
    public function testValidInstructions($instructions, $assertion)
    {
        $def = new InstructionsDefinition();
        $def->setValue($instructions);
        $this->assertEquals(implode("\n", $def->getValue()), $assertion);
    }

    /**
     * @dataProvider invalidInstructionsProvider
     * @expectedException \Frizinak\Corked\Definition\Exception\ValidationException
     */
    public function testInvalidInstructions($instructions)
    {
        $def = new InstructionsDefinition();
        $def->setValue($instructions);
    }
}
