<?php
namespace Craft;

use Cake\Utility\Hash as Hash;

class UserFeedMeElementType extends BaseFeedMeElementType
{
    // Templates
    // =========================================================================

    public function getGroupsTemplate()
    {
        return 'feedme/_includes/elements/user/groups';
    }

    public function getColumnTemplate()
    {
        return 'feedme/_includes/elements/user/column';
    }

    public function getMappingTemplate()
    {
        return 'feedme/_includes/elements/user/map';
    }


    // Public Methods
    // =========================================================================

    public function getGroups()
    {
        $result = false;

        // User are only allowed for Craft Pro
        if (craft()->getEdition() == Craft::Pro) {
            $groups = craft()->userGroups->getAllGroups();

            $result = count($groups) ? $groups : true;
        }

        return $result;
    }

    public function setModel($settings)
    {
        $element = new UserModel();

        if ($settings['locale']) {
            $element->locale = $settings['locale'];
        }

        return $element;
    }

    public function setCriteria($settings)
    {
        $criteria = craft()->elements->getCriteria(ElementType::User);
        $criteria->status = null;
        $criteria->limit = null;
        //$criteria->group = null;
        $criteria->localeEnabled = null;
        
        if ($settings['locale']) {
            $criteria->locale = $settings['locale'];
        }

        return $criteria;
    }

    public function matchExistingElement(&$criteria, $data, $settings)
    {
        foreach ($settings['fieldUnique'] as $handle => $value) {
            if ((int)$value === 1) {
                $feedValue = Hash::get($data, $handle);
                $feedValue = Hash::get($data, $handle . '.data', $feedValue);

                if ($feedValue) {
                    $criteria->$handle = DbHelper::escapeParam($feedValue);
                } else {
                    FeedMePlugin::log('User: no data for `' . $handle . '` to match an existing element on. Is data present for this in your feed?', LogLevel::Error, true);
                    return false;
                }
            }
        }

        // Check to see if an element already exists - interestingly, find()[0] is faster than first()
        $elements = $criteria->find();

        if (count($elements)) {
            return $elements[0];
        }

        return null;
    }

    public function delete(array $elements)
    {
        $return = true;

        // Delete users
        foreach ($elements as $element) {
            if (!craft()->users->deleteUser($element)) {
                $return = false;
            }
        }

        return $return;
    }

    public function prepForElementModel(BaseElementModel $element, array &$data, $settings)
    {
        foreach ($data as $handle => $value) {
            if (is_null($value)) {
                continue;
            }

            if (isset($value['data']) && $value['data'] === null) {
                continue;
            }

            if (is_array($value)) {
                $dataValue = Hash::get($value, 'data', null);
            } else {
                $dataValue = $value;
            }

            // Check for any Twig shorthand used
            if (is_string($dataValue)) {
                $objectModel = $this->getObjectModel($data);
                $dataValue = craft()->templates->renderObjectTemplate($dataValue, $objectModel);
            }
            
            switch ($handle) {
                case 'id':
                case 'username':
                case 'firstName':
                case 'lastName':
                case 'email':
                case 'preferredLocale':
                case 'newPassword':
                    $element->$handle = $dataValue;
                    break;
                case 'photo':
                    $this->_handleUserPhoto($element, $dataValue);
                    break;
                case 'status':
                    $this->_setUserStatus($element, $dataValue);
                    break;
                default:
                    continue 2;
            }

            // Update the original data in our feed - for clarity in debugging
            $data[$handle] = $element->$handle;
        }

        // Set email as username
        if (craft()->config->get('useEmailAsUsername')) {
            $element->username = $element->email;
        }

        return $element;
    }

    public function save(BaseElementModel &$element, array $data, $settings)
    {
        // Because our main processing function checks for locale-only content, the content field won't be
        // prepped with data. However - user profile fields aren't multi-locale, and often validation will fail.
        // So pretty much ignore local-targeting (because there's only one), and put back the content
        $element->setContentFromPost($data);
        
        if (craft()->users->saveUser($element)) {
            // Check for any existing groups this user exists on
            $groups = array();

            if ($element->groups) {
                foreach ($element->groups as $group) {
                    $groups[] = $group->id;
                }
            }

            $newGroupId = $settings['elementGroup']['User'];

            if (!in_array($newGroupId, $groups)) {
                $groups[] = $newGroupId;
            }

            craft()->userGroups->assignUserToGroups($element->id, $groups);
            
            return true;
        }

        return false;
    }

    public function afterSave(BaseElementModel $element, array $data, $settings)
    {
        
    }


    // Private Methods
    // =========================================================================

    private function _handleUserPhoto(UserModel $user, $filename)
    {
        $photo = craft()->path->getUserPhotosPath() . $filename;

        if (!IOHelper::fileExists($photo)) {
            return false;
        }

        $image = craft()->images->loadImage($photo);
        $imageWidth = $image->getWidth();
        $imageHeight = $image->getHeight();

        $dimension = min($imageWidth, $imageHeight);
        $horizontalMargin = ($imageWidth - $dimension) / 2;
        $verticalMargin = ($imageHeight - $dimension) / 2;
        $image->crop($horizontalMargin, $imageWidth - $horizontalMargin, $verticalMargin, $imageHeight - $verticalMargin);

        craft()->users->saveUserPhoto($filename, $image, $user);
    }

    private function _setUserStatus(UserModel $user, $status)
    {
        switch ($status) {
            case 'locked';
                $user->locked = true;
                break;
            case 'suspended';
                $user->locked = false;
                $user->suspended = true;
                break;
            case 'archived':
                $user->locked = false;
                $user->suspended = false;
                $user->archived = true;
                break;
            case 'pending':
                $user->locked = false;
                $user->suspended = false;
                $user->archived = false;
                $user->pending = true;
                break;
            case 'active':
                $user->suspended = false;
                $user->locked = false;
                $user->setActive();
                break;
        }

        return $user;
    }
}