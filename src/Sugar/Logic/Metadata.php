<?php

// Enrico Simonetti
// enricosimonetti.com

namespace Toothpaste\Sugar\Logic;
use Toothpaste\Sugar\Instance;
use Toothpaste\Sugar;

class Metadata extends Sugar\BaseLogic
{
    protected function detectPossibleIssueWithFilterOperatorsCustomisations()
    {
        // attempt to detect a possible problem that was encountered on an instance where filters operators has a require_once instead of require
        // this type of customisation would possibly corrupt every subsequent user's metadata automated generation

        $mm = new \MetaDataManager();
        $files = \MetaDataFiles::getClientFiles($mm->getPlatformList(), 'filter');
        if (!empty($files)) {
            foreach ($files as $file) {
                $content = file_get_contents($file['path']);
                // look for require_once and notify that it should be changed to a require
                if (preg_match('/require_once/', $content) || preg_match('/include_once/', $content)) {
                    $this->writeln('It was found a possible require_once() or include_once() on the following file ' . $file['path'] . '.');
                    $this->writeln('As those functions are known to cause problems with the metadata cache warm-up of multiple subsequent users,
                        please convert it to a require() or include(), to proceed futher with this script.');
                    return true;
                }
            }
        }
        return false;
    }

    protected function detectKnownCustomisationsConflicts()
    {
        if ($this->detectPossibleIssueWithFilterOperatorsCustomisations()) {
            return true;
        }

        return false;
    }

    protected function getAdminUserId()
    {
        if (!empty($GLOBALS['current_user']->id)) {
            return $GLOBALS['current_user']->id;
        } else {
            return \BeanFactory::newBean('Users')->getSystemUser()->id;
        }
    }

    protected function getUniqueMetadataCombinations()
    {
        $this->writeln('Detecting unique metadata (users/roles) combinations');

        // user based metadata, with last logged in users first
        $sq = new \SugarQuery();
        $sq->from(\BeanFactory::newBean('Users'));
        $sq->select(array('id'));
        $sq->where()->equals('deleted', 0);
        $sq->where()->equals('status', 'Active');
        $sq->where()->equals('is_admin', 0);
        $sq->where()->equals('is_group', 0);
        $sq->orderBy('last_login', 'desc');
        $records = $sq->execute();

        $this->writeln(count($records) . ' non-admin active users found in the current system');

        // add system user to the list
        $users = array_merge(['id' => $this->getAdminUserId()], $records);

        // store only the first user/roles metadata combination for the same hash
        $contexts = [];
        foreach ($users as $record) {
            $user = \BeanFactory::getBean('Users', $record['id']);
            if (!empty($user->id)) {
                $context = new \MetaDataContextUser($user);
                if (empty($contexts[$context->getHash()])) {
                    $contexts[$context->getHash()]['context'] = $context;
                    $contexts[$context->getHash()]['user_id'] = $user->id;
                }
            }
        }

        $this->writeln(count($contexts) . ' unique users/roles combinations found in the current system');

        return $contexts;
    }

    public function generate()
    {
        global $current_user;

        // we need the current user set correctly to make sure the result is as expected
        $originalUser = clone($current_user);

        $this->writeln('Refreshing the current metadata cache');
        \MetaDataManager::refreshCache();

        // as all cache/api/metadata/lang_* file's md5 generated automatically differ
        // after further analysis the only difference appears to be "LBL_ALT_HOT_KEY":"Ctrl+" -> "LBL_ALT_HOT_KEY":"Alt+"
        // it is due to what "browser" the repair functionality thinks is running it, with the utils function get_alt_hot_key
        // eg: linux on cli vs mac on the browser

        global $current_language;

        Instance::basicWarmUp();
        \MetaDataManager::setupMetadata(['base'], [$current_language]);

        if (!$this->detectKnownCustomisationsConflicts()) {
            $contexts = $this->getUniqueMetadataCombinations();
            if (!empty($contexts)) {
                // build only one metadata per hash, not for every user
                $this->writeln('Rebuilding users/roles metadata combinations');
                $mm = new \MetadataManager();
                foreach ($contexts as $content) {
                    if (!empty($content['user_id'])) {
                        $user = \BeanFactory::getBean('Users', $content['user_id']);
                        $current_user = $user;
                        $mm->getMetadata([], $content['context']);
                        $this->write('.');
                    }
                }
                $this->writeln('');
                $this->writeln('Generated successfuly ' . count($contexts) . ' unique users/roles metadata combinations');
            }
        } else {
            $this->writeln('The metadata generation has not been processed due to a known customisation conflict');
        }

        // put back the original user
        $current_user = $originalUser;
    }

    public function extractContent($directory)
    {
        if (!empty($directory)) {
            $directory = $this->addTrailingSlash($directory);
            $this->createDir($directory);

            $this->writeln('Retrieving metadata');

            $qb = \DBManagerFactory::getConnection()->createQueryBuilder();
            $qb->select(['type', 'data'])
                ->from('metadata_cache')
                ->where($qb->expr()->eq('deleted', $qb->createPositionalParameter(0)))
                ->orderBy('type');
            $res = $qb->execute();

            $results = [];
            while ($row = $res->fetch()) {
                $row['data'] = unserialize(gzinflate(base64_decode($row['data'])));
                $results[] = $row;
            }

            $fileName = $directory . 'metadata_' . gmdate('Y_m_d_H_i_s') . '_' . microtime(true) . '.array';
            $this->writeln('Saving metadata on disk in a plaintext array into ' . $fileName);
            file_put_contents($fileName, print_r($results, true));
        } else {
            $this->writeln('Please provide a valid destination directory');
        }
    }
}
