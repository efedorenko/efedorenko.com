<?php
namespace Craft;

class FeedMeVariable
{
    public function getPlugin()
    {
        return craft()->plugins->getPlugin('feedMe');
    }

    public function getPluginUrl()
    {
        return $this->getPlugin('feedMe')->getPluginUrl();
    }

    public function getPluginName()
    {
        return $this->getPlugin('feedMe')->getName();
    }

    public function getPluginVersion()
    {
        return $this->getPlugin()->getVersion();
    }

    public function getSelectOptions($options, $label = 'name', $index = 'id', $includeNone = true) {
        $values = array();

        if ($includeNone) {
            $values[''] = 'None';
        }

        if (is_array($options)) {
            foreach ($options as $key => $value) {
                $values[$value[$index]] = $value[$label];
            }
        }

        return $values;
    }

    public function getElementTypeMapping($elementType)
    {
        return craft()->feedMe_elementTypes->getElementTypeMapping($elementType);
    }

    public function getElementTypeGroups($elementType)
    {
        if ($service = craft()->feedMe->getElementTypeService($elementType)) {
            return $service->getGroups();
        }

        return false;
    }

    public function getElementTypeGroupsTemplate($elementType)
    {
        if ($service = craft()->feedMe->getElementTypeService($elementType)) {
            return $service->getGroupsTemplate();
        }

        return false;
    }

    public function getElementTypeColumnTemplate($elementType)
    {
        if ($service = craft()->feedMe->getElementTypeService($elementType)) {
            return $service->getColumnTemplate();
        }

        return false;
    }

    public function getEntryTypeById($entryTypeId)
    {
        return craft()->sections->getEntryTypeById($entryTypeId);
    }

    public function feed($options = array())
    {
        return craft()->feedMe_data->getFeedForTemplate($options);
    }

    public function getFeeds()
    {
        $result = array();

        $feeds = craft()->feedMe_feeds->getFeeds();

        foreach ($feeds as $key => $feed) {
            $result[$feed->id] = $feed->name;
        }

        return $result;
    }

    public function isProEdition()
    {
        return craft()->feedMe_license->isProEdition();
    }

    //
    // Fields + Field Mapping
    //

    public function getFieldMapping($fieldHandle)
    {
        return craft()->feedMe_fields->getFieldMapping($fieldHandle);
    }

    public function formatDateTime($dateTime)
    {
        return DateTime::createFromString($dateTime, craft()->getTimeZone());
    }



    // Helper function for handling Matrix fields
    public function getMatrixBlocks($fieldId)
    {
        return craft()->matrix->getBlockTypesByFieldId($fieldId);
    }

    // Commerce doesn't have a getProductTypeById() function
    public function getProductTypeById($productTypeId)
    {
        return craft()->commerce_productTypes->getProductTypeById($productTypeId);
    }


    // Helper functions for element fields in getting their inner-element field layouts
    public function getAssetFieldLayout($settings)
    {
        if (empty($settings['useSingleFolder'])) {
            $folderSourceId = $settings['defaultUploadLocationSource'];
        } else {
            $folderSourceId = $settings['singleUploadLocationSource'];
        }

        $source = craft()->assetSources->getSourceById($folderSourceId);

        if (!$source) {
            return false;
        }

        $layoutId = $source->fieldLayoutId;
        return craft()->fields->getLayoutById($layoutId);
    }

    public function getCategoriesFieldLayout($categoryGroup)
    {
        // Craft throws an error if there are no Categories at all
        if (!craft()->categories->getAllGroupIds()) {
            return false;
        }

        // Get the Category Group ID
        $id = str_replace('group:', '', $categoryGroup);
        $group = craft()->categories->getGroupById($id);

        if (!$group) {
            return false;
        }

        // Get the field layout for this Category Group
        $layoutId = $group->fieldLayoutId;
        return craft()->fields->getLayoutById($layoutId);
    }

    public function getTagsFieldLayout($tagGroup)
    {
        // Get the Tag Group ID
        $id = str_replace('taggroup:', '', $tagGroup);
        $group = craft()->tags->getTagGroupById($id);

        if (!$group) {
            return false;
        }

        // Get the field layout for this Tag Group
        $layoutId = $group->fieldLayoutId;
        return craft()->fields->getLayoutById($layoutId);
    }

    public function getAssetSourceById($id)
    {
        return craft()->assetSources->getSourceById($id);
    }

    public function getClientUsers()
    {
        $criteria = craft()->elements->getCriteria(ElementType::User);
        $criteria->status = null;
        
        return $criteria->find();
    }






}
