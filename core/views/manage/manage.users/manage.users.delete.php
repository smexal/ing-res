<?php

namespace Forge\Core\Views;

use Forge\Core\Abstracts as Abstracts;

class ManageDeleteUser extends Abstracts\View {
    public $parent = 'users';
    public $permission = 'manage.users.delete';
    public $name = 'delete';

    public function content($uri=array()) {
        if(is_array($uri)) {
            if($uri[0] == "cancel") {
                App::instance()->redirect(Utils::getUrl(array('manage', 'users')));
            }
            $id = $uri[0];
            if(count($uri) > 1) {
                if($uri[1] == 'confirmed') {
                    // delete user
                    if(User::delete($id)) {
                        // success
                        App::instance()->addMessage(i('User has been deleted.'), "success");
                    } else {
                        // failed
                        App::instance()->addMessage(i('There was an error while deleting the user.'), "danger");
                    }
                    App::instance()->redirect(Utils::getUrl(array('manage', 'users')));
                }
            } else {
                return $this->confirmationScreen($id);
            }
        }
    }

    private function confirmationScreen($id) {
      // display confirm screen;
      $user = new User($id);
      return $this->app->render(CORE_TEMPLATE_DIR."assets/", "confirm", array(
          "title" => sprintf(i('Delete user \'%s\'?'), $user->get('username')),
          "message" => sprintf(i('Do you really want to delete user with the email \'%s\'?'), $user->get('email')),
          "yes" => array(
              "title" => i('Yes, delete user'),
              "url" => Utils::getUrl(array("manage", "users", "delete", $id, "confirmed"))
          ),
          "no" => array(
              "title" => i("No, cancel."),
              "url" => Utils::getUrl(array("manage", "users", "delete", "cancel"))
          )
      ));
    }
}

?>
