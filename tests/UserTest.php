// tests/Entity/UserTest.php
namespace App\Tests\Entity;

use App\Entity\User;
use App\Entity\Role;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testAddRole()
    {
        // Créer un utilisateur
        $user = new User();
        $user->setEmail('user@example.com');
        $user->setPassword('password123');

        // Créer un rôle
        $role = new Role();
        $role->setNomRole('ROLE_USER');

        // Ajouter le rôle à l'utilisateur
        $user->addRole($role);

        // Vérifier que le rôle a été ajouté
        $this->assertContains('ROLE_USER', $user->getRoles());
        $this->assertCount(1, $user->getRolesCollection());
    }
}