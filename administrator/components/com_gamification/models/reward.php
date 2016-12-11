<?php
/**
 * @package      Gamification Platform
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2016 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

use Joomla\Utilities\ArrayHelper;

// no direct access
defined('_JEXEC') or die;

// Register Observers
JLoader::register('GamificationObserverReward', GAMIFICATION_PATH_COMPONENT_ADMINISTRATOR .'/tables/observers/reward.php');
JObserverMapper::addObserverClassToClass('GamificationObserverReward', 'GamificationTableReward', array('typeAlias' => 'com_gamification.reward'));

class GamificationModelReward extends JModelAdmin
{
    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param   string $type   The table type to instantiate
     * @param   string $prefix A prefix for the table class name. Optional.
     * @param   array  $config Configuration array for model. Optional.
     *
     * @return  GamificationTableReward
     * @since   1.6
     */
    public function getTable($type = 'Reward', $prefix = 'GamificationTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Method to get the record form.
     *
     * @param   array   $data     An optional array of data for the form to interrogate.
     * @param   boolean $loadData True if the form is to load its own data (default case), false if not.
     *
     * @return  JForm   A JForm object on success, false on failure
     * @since   1.6
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm($this->option . '.reward', 'reward', array('control' => 'jform', 'load_data' => $loadData));
        if (!$form) {
            return false;
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  mixed   The data for the form.
     * @since   1.6
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = JFactory::getApplication()->getUserState($this->option . '.edit.reward.data', array());
        if (!$data) {
            $data = $this->getItem();
            
            if ((int)$data->points_number === 0) {
                $data->points_number = '';
            }
        }

        return $data;
    }

    /**
     * Save data into the DB
     *
     * @param array $data The data about item
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws \RuntimeException
     *
     * @return  int
     */
    public function save($data)
    {
        $id        = ArrayHelper::getValue($data, 'id');
        $title     = ArrayHelper::getValue($data, 'title');
        $groupId   = ArrayHelper::getValue($data, 'group_id', 0, 'int');
        $published = ArrayHelper::getValue($data, 'published', 0, 'int');
        $description = ArrayHelper::getValue($data, 'description');

        // Get advanced options.
        $activityText = ArrayHelper::getValue($data, 'activity_text');
        $number       = ArrayHelper::getValue($data, 'number');
        $note         = ArrayHelper::getValue($data, 'note');
        $points       = ArrayHelper::getValue($data, 'points_number', 0, 'int');
        $pointsId     = ArrayHelper::getValue($data, 'points_id', 0, 'int');

        if (!is_numeric($number) and !$number) {
            $number = null;
        }

        if (!$note) {
            $note = null;
        }

        if (!$description) {
            $description = null;
        }

        if (!$activityText) {
            $activityText = null;
        }

        // Load a record from the database
        $row = $this->getTable();
        /** @var $row GamificationTableReward */

        $row->load($id);

        $row->set('title', $title);
        $row->set('points_number', $points);
        $row->set('points_id', $pointsId);
        $row->set('group_id', $groupId);
        $row->set('published', $published);
        $row->set('note', $note);
        $row->set('description', $description);
        $row->set('activity_text', $activityText);
        $row->set('number', $number);

        $this->prepareImage($row, $data);

        $row->store(true);

        return $row->get('id');
    }

    /**
     * Prepare and sanitise the table prior to saving.
     *
     * @param GamificationTableReward $table
     * @param array                  $data
     *
     * @throws \UnexpectedValueException
     *
     * @since    1.6
     */
    protected function prepareImage($table, $data)
    {
        if (!empty($data['image'])) {
            // Delete old image if I upload new one.
            if ($table->get('image')) {
                $params     = JComponentHelper::getParams($this->option);
                /** @var  $params Joomla\Registry\Registry */

                $filesystemHelper   = new Prism\Filesystem\Helper($params);
                $mediaFolder        = $filesystemHelper->getMediaFolder();

                $fileImage  = JPath::clean(JPATH_ROOT .DIRECTORY_SEPARATOR .$mediaFolder. DIRECTORY_SEPARATOR. $table->get('image'));
                $fileSmall  = JPath::clean(JPATH_ROOT .DIRECTORY_SEPARATOR .$mediaFolder. DIRECTORY_SEPARATOR. $table->get('image_small'));
                $fileSquare = JPath::clean(JPATH_ROOT .DIRECTORY_SEPARATOR .$mediaFolder. DIRECTORY_SEPARATOR. $table->get('image_square'));

                if (is_file($fileImage)) {
                    JFile::delete($fileImage);
                }

                if (is_file($fileSmall)) {
                    JFile::delete($fileSmall);
                }

                if (is_file($fileSquare)) {
                    JFile::delete($fileSquare);
                }

            }
            $table->set('image', $data['image']);
            $table->set('image_small', $data['image_small']);
            $table->set('image_square', $data['image_square']);
        }
    }

    public function removeImage($id)
    {
        // Load a record from the database
        $row = $this->getTable();
        $row->load($id);

        if ($row->get('image')) {
            $params     = JComponentHelper::getParams($this->option);
            /** @var  $params Joomla\Registry\Registry */

            $filesystemHelper   = new Prism\Filesystem\Helper($params);
            $mediaFolder        = $filesystemHelper->getMediaFolder();

            $fileImage  = JPath::clean(JPATH_ROOT . DIRECTORY_SEPARATOR .$mediaFolder. DIRECTORY_SEPARATOR . $row->get('image'));
            $fileSmall  = JPath::clean(JPATH_ROOT . DIRECTORY_SEPARATOR .$mediaFolder. DIRECTORY_SEPARATOR . $row->get('image_small'));
            $fileSquare = JPath::clean(JPATH_ROOT . DIRECTORY_SEPARATOR .$mediaFolder. DIRECTORY_SEPARATOR . $row->get('image_square'));

            if (is_file($fileImage)) {
                JFile::delete($fileImage);
            }

            if (is_file($fileSmall)) {
                JFile::delete($fileSmall);
            }

            if (is_file($fileSquare)) {
                JFile::delete($fileSquare);
            }
        }

        $row->set('image', null);
        $row->set('image_small', null);
        $row->set('image_square', null);
        $row->store(true);
    }

    /**
     * Store the file in a folder of the extension.
     *
     * @param array $uploadedFileData
     * @param bool $resizeImage
     *
     * @throws \RuntimeException
     * @throws \Exception
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws \LogicException
     *
     * @return array
     */
    public function uploadImage(array $uploadedFileData, $resizeImage = false)
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        $uploadedFile = ArrayHelper::getValue($uploadedFileData, 'tmp_name');
        $uploadedName = ArrayHelper::getValue($uploadedFileData, 'name');
        $errorCode    = ArrayHelper::getValue($uploadedFileData, 'error');

        $params     = JComponentHelper::getParams($this->option);
        /** @var  $params Joomla\Registry\Registry */

        $filesystemHelper   = new Prism\Filesystem\Helper($params);
        $mediaFolder        = $filesystemHelper->getMediaFolder();

        $destinationFolder  = JPath::clean(JPATH_ROOT .'/'. $mediaFolder, '/');

        // Joomla! media extension parameters
        $mediaParams = JComponentHelper::getParams('com_media');
        /** @var $mediaParams Joomla\Registry\Registry */

        // Prepare size validator.
        $KB            = pow(1024, 2);
        $fileSize      = ArrayHelper::getValue($uploadedFileData, 'size', 0, 'int');
        $uploadMaxSize = $mediaParams->get('upload_maxsize') * $KB;

        // Prepare file validators.
        $sizeValidator   = new Prism\File\Validator\Size($fileSize, $uploadMaxSize);
        $serverValidator = new Prism\File\Validator\Server($errorCode, array(UPLOAD_ERR_NO_FILE));
        $imageValidator  = new Prism\File\Validator\Image($uploadedFile, $uploadedName);

        // Get allowed mime types from media manager options
        $mimeTypes = explode(',', $mediaParams->get('upload_mime'));
        $imageValidator->setMimeTypes($mimeTypes);

        // Get allowed image extensions from media manager options
        $imageExtensions = explode(',', $mediaParams->get('image_extensions'));
        $imageValidator->setImageExtensions($imageExtensions);

        $file = new Prism\File\File($uploadedFile);
        $file
            ->addValidator($sizeValidator)
            ->addValidator($imageValidator)
            ->addValidator($serverValidator);

        // Validate the file
        if (!$file->isValid()) {
            throw new RuntimeException($file->getError());
        }

        // Upload the file in temporary folder.
        $temporaryFolder = JPath::clean($app->get('tmp_path'), '/');
        $filesystemLocal = new Prism\Filesystem\Adapter\Local($temporaryFolder);
        $sourceFile      = $filesystemLocal->upload($uploadedFileData);

        if (!JFile::exists($sourceFile)) {
            throw new RuntimeException('COM_GAMIFICATION_ERROR_FILE_CANT_BE_UPLOADED');
        }

        $names = array(
            'image'        => '',
            'image_small'  => '',
            'image_square' => ''
        );

        // Create main image
        $options = new Joomla\Registry\Registry();
        $options->set('filename_length', 16);
        $options->set('suffix', '_reward_image');
        $options->set('scale', $params->get('image_resizing_scale', \JImage::SCALE_INSIDE));
        $options->set('quality', $params->get('image_quality', Prism\Constants::QUALITY_HIGH));

        $image   = new Prism\File\Image($sourceFile);

        // Create main image
        if (!$resizeImage) {
            $result  = $image->toFile($destinationFolder, $options);
            $names['image'] = $result['filename'];
        } else {
            $options->set('width', $params->get('image_width', 200));
            $options->set('height', $params->get('image_height', 200));

            $result  = $image->resize($destinationFolder, $options);
            $names['image'] = $result['filename'];
        }

        // Create small image
        $options->set('width', $params->get('image_small_width', 100));
        $options->set('height', $params->get('image_small_height', 100));
        $options->set('suffix', '_reward_small');
        $result  = $image->resize($destinationFolder, $options);
        $names['image_small'] = $result['filename'];

        // Create square image
        $options->set('width', $params->get('image_square_width', 50));
        $options->set('height', $params->get('image_square_height', 50));
        $options->set('suffix', '_reward_square');
        $result  = $image->resize($destinationFolder, $options);
        $names['image_square'] = $result['filename'];

        // Remove the temporary file.
        if (JFile::exists($sourceFile)) {
            JFile::delete($sourceFile);
        }

        return $names;
    }
}
