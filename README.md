# FORGE
...yo

## Modules
...

### Proposed Folder Structure
The following module structure is proposed but not mandatory for a module. We try to keep 'em all the same for recognition.
The Forge core provides fully automatic autoloading for modules which are built in the default strcture. Check the Autoload and Autoregister Sections for more informations  
```
<module-name>/
|___.noautoload       (Optional. See Autoload-Section)
|___autoregister.json (Optional. See Autoregister-Section)
|___assets/
|   |___css/
|   |___images/
|   |___scripts/
|___classes/
|   |___externals/
|___components/
|___collections/
|___templates/
|   |___cache/
|___views/
|___module.php
```

### Example module.php
```php
<?php

namespace Forge\Modules\<YourNamespace>;

use \Forge\Core\Abstracts\Module as AbstractModule;



class Module extends AbstractModule {

    public function setup() {
        $this->version = '1.0.0';
        $this->id = "forge-module-name";
        $this->name = i('Module name', 'textdomain');
        $this->description = i('Describe your module.', 'textdomain');
        $this->image = $this->url().'assets/images/module-image.png';
    }

    public function start() {
    }

}

?>
```

## Autoloading
Forge provides fully automatic autoloading of PHP-classes. Forge does this automatically inside the whole forge directory.
In order for Forge to do this correctly, your class files have to:
* Define a namespace
* Have only one class / interface / trait declaration inside it


The autoloading process caches your files. Thus you have to flush the cache when changing your config or adding a TYPE. 
You can do this by setting the constant `AUTOLOADER_CLASS_FLUSH` to true or setting the GET-Parameter `flushac`

### Disable Autoloading
You can disable the autoloading in your theme / module by adding a .noautoload file inside your root directory. 
The .noautoload file can also be placed inside a subdirectory in order to avoid autoloading specific files. E.g. Libraries which are used by your module. 

## Autoregistration
Forge provides the possibility to automatically register standard forge components like collections, views or components. In order for your module / theme to enable the auto registration, you have to add a autoregister.json File in your module root directory.

The autoregistration process caches your files. Thus you have to flush the cache when changing your config or adding a TYPE. 
You can do this by setting the constant `MANAGER_CACHE_FLUSH` to true or setting the GET-Parameter `flushmc`.


## autoregister.json
Following are the possible configuration-properties for the autoregister Files
The TYPE: is either views, components or collections
```
{
 "namespace": "\\My\\Namespace", // (required) The base namespace for the module with no trailing slash
 "nsfromtype": true,             // (optional) Adds the matching TYPE as a sub-package E.g: \My\Namespace\TYPE
 "disabled": [TYPE1, TYPE2],     // (optional) List of the types which shall not be autoregistered
 "TYPE": {                       // (optional) Add a Type-Array to make specific configurations
     "folder": "foldername",     // (optional) Defines a custom folder instad of the default (TYPE)
     "package": "SPECIALNS"      // (optional) Defines a custom sub-package for the TYPE. E.G: \My\Namespace\SPECIALNS
 } 
}
```

# Collections, Metas, Relations

## Collections
TBD

## Metas
TBD

## Relations

### Registering new relations in the Relation Directory
Example at: https://github.com/smexal/forge-tournaments/blob/master/module.php#L65
            https://github.com/smexal/forge-tournaments/blob/master/collections/collection.phase.php#L42
```            
 \registerModifier('Forge/Core/RelationDirectory/collectRelations', 
    'my_new_relations');
function my_new_relations($existing) {
    return array_merge($existing, [
        'forge_teams-team_teammember' => new CollectionRelation(
            // This is the name in the db-column "name"
            'forge_teams-team_teammember',
            TeamCollection::COLLECTION_NAME, 
            TeamMemberCollection::COLLECTION_NAME, 
            RelationDirection::DIRECTED
        )
    ]);
}
```

### Retrieving new relations
Example at: https://github.com/smexal/forge/blob/master/core/classes/class.fieldloader.php#L32
```
$relation = $field['relation'];
$relation = App::instance()->rd->getRelation($relation['identifier']);
// The special case of Direction::REVERSED is not yet implemented here
$list_of_ids = $relation->getOfLeft($item->id, Prepares::AS_IDS_RIGHT);

// Directly generates CollectionItems iff the Relation registered is a CollectionRelation
$list_of_collections = $relation->getOfLeft($item->id, Prepares::AS_INSTANCE_RIGHT);
```

### Saving or Adding relations
Example at: https://github.com/smexal/forge/blob/master/core/classes/class.fieldsaver.php#L32
```
$relation = $field['relation'];
$rel = App::instance()->rd->getRelation($relation['identifier']);
// Maxes a diff from the items in the DB then removes the missing won in $right_item_ids and adds the new one
$rel->setRightItems($item->id, $right_item_ids);
// There is no interface for setting via an object, always an ID
$rel->add($r_item->id, $r_item->id);
$rel->addMultiple($r_item->id, [42, 1337, 80085]);
```
