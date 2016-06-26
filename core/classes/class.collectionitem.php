<?php

class CollectionItem {
    public $id = null;

    private $db = null;
    private $base_data = null;
    private $meta = null;

    public function __construct($id) {
        $this->id = $id;
        $this->db = App::instance()->db;

        $this->db->where('item', $this->id);
        $this->meta = $this->db->get('collection_meta');

        $this->db->where('id', $this->id);
        $this->base_data = $this->db->getOne('collections');
    }

    public function getName() {
        return $this->base_data['name'];
    }

    public function getMeta($key, $lang = false) {
        if(!$lang && $lang !== 0) {
            $lang = Localization::getCurrentLanguage();
        }
        foreach($this->meta as $meta) {
            if($meta['keyy'] == $key && $meta['lang'] == $lang) {
                if(Utils::isJSON($meta['value'])) {
                    return json_decode($meta['value']);
                }
                return $meta['value'];
            }
        }
        return false;
    }

    public function updateMeta($key, $value, $language) {
        if(!$language) {
            $language = 0;
        }
        $current_value = $this->getMeta($key, $language);
        if(is_array($current_value)) {
            $current_value = json_encode($current_value);
        }
        if(strlen($value) == 0) {
            // remove meta value, if there is no value
            $this->deleteMeta($key, $language);
        }
        if($current_value) {
            // update with new
            $this->setMeta($key, $value, $language);
        } else {
            // insert new value
            $this->insertMeta($key, $value, $language);
        }
    }

    public function deleteMeta($key, $language) {
        $this->db->where('keyy', $key);
        $this->db->where('lang', $language);
        $this->db->delete('collection_meta');
    }

    public function setMeta($key, $value, $language) {
        $this->db->where('keyy', $key);
        $this->db->where('item', $this->id);
        $this->db->where('lang', $language);
        $this->db->update('collection_meta', array(
            'value' => $value
        ));
    }

      public function insertMeta($key, $value, $language) {
        if(strlen($value) == 0) {
            return;
            // don't save if we don't have anything to save...
        }
        $this->db->insert('collection_meta', array(
            'keyy' => $key,
            'lang' => $language,
            'item' => $this->id,
            'value' => $value
        ));
    }
}

?>