<?php

$PluginInfo['categoryunreadphoto'] = [
    'Name' => 'Unread Category Photo',
    'Description' => 'Allows setting a second category photo to indicate that a category contains unread comments.',
    'Version' => '0.1',
    'MobileFriendly' => true,
    'Author' => 'Bleistivt',
    'AuthorUrl' => 'http://bleistivt.net',
    'License' => 'GNU GPL2'
];

class CategoryUnreadPhotoPlugin extends Gdn_Plugin {

    public function categoriesController_render_before($sender) {
        foreach ($sender->data('Categories', []) as $id => $category) {
            if (!$category['Read'] && $category['UnreadPhoto']) {
                $sender->setData(
                    'Categories.'.$id.'.PhotoUrl',
                    Gdn_Upload::url($category['UnreadPhoto'])
                );
            }
        }
    }


    public function settingsController_afterCategorySettings_handler($sender) {
        $delete = anchor(
            t('Delete Photo'),
            'plugin/deleteunreadphoto/'
                .$sender->Category->CategoryID.'/'
                .Gdn::session()->transientKey(),
            'SmallButton PopConfirm'
        );
        echo wrap(
            $sender->Form->label('Unread Photo', 'UnreadPhoto')
                .$sender->Form->imageUpload('UnreadPhoto')
                .($sender->Form->getValue('UnreadPhoto') ? $delete : ''),
            'li'
        );
    }


    public function settingsController_addEditCategory_handler($sender) {
        if ($sender->Form->authenticatedPostBack()) {
            $upload = new Gdn_Upload();
            if ($tmp = $upload->validateUpload('UnreadPhoto_New', false)) {
                $target = $upload->generateTargetName(PATH_UPLOADS);
                $parts = $upload->saveAs($tmp, $target);
                $sender->Form->setFormValue('UnreadPhoto', $parts['SaveName']);
            }
        }
    }


    public function pluginController_deleteUnreadPhoto_create($sender, $id = false, $tkey = '') {
        $sender->permission('Garden.Settings.Manage');
        $category = CategoryModel::categories($id);
        if (Gdn::session()->validateTransientKey($tkey) && $category) {
            (new CategoryModel())->setField($id, 'UnreadPhoto', null);
            (new Gdn_Upload())->delete($category['UnreadPhoto']);
        }
        redirect('vanilla/settings/editcategory/'.$id);
    }


    public function structure() {
        Gdn::structure()
            ->table('Category')
            ->column('UnreadPhoto', 'varchar(255)', true)
            ->set();
    }


    public function setup() {
        $this->structure();
    }

}