<?php
declare(strict_types=1);

class UserModel extends Model
{
    public function findByEmail(string $email): array|false
    {
        $stmt = $this->db->prepare('
            SELECT u.*, r.libelle AS role
            FROM utilisateur u
            JOIN role r ON u.role_id = r.role_id
            WHERE u.email = :email AND u.actif = 1
        ');
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare('
            INSERT INTO utilisateur 
            (role_id, email, password, nom, prenom, telephone, adresse, ville, code_postal, pays)
            VALUES (3, :email, :password, :nom, :prenom, :telephone, :adresse, :ville, :code_postal, :pays)
        ');
        return $stmt->execute([
            ':email'       => $data['email'],
            ':password'    => password_hash($data['password'], PASSWORD_BCRYPT),
            ':nom'         => $data['nom'],
            ':prenom'      => $data['prenom'],
            ':telephone'   => $data['telephone'],
            ':adresse'     => $data['adresse'],
            ':ville'       => $data['ville'],
            ':code_postal' => $data['code_postal'],
            ':pays'        => $data['pays'],
        ]);
    }

    public function getById(int $id): array|false
    {
        $stmt = $this->db->prepare('
            SELECT u.*, r.libelle AS role
            FROM utilisateur u
            JOIN role r ON u.role_id = r.role_id
            WHERE u.utilisateur_id = :id
        ');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }
    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare('
            UPDATE utilisateur SET
                nom         = :nom,
                prenom      = :prenom,
                telephone   = :telephone,
                adresse     = :adresse,
                ville       = :ville,
                code_postal = :code_postal
            WHERE utilisateur_id = :id
        ');
        return $stmt->execute([
            ':nom'         => $data['nom'],
            ':prenom'      => $data['prenom'],
            ':telephone'   => $data['telephone'],
            ':adresse'     => $data['adresse'],
            ':ville'       => $data['ville'],
            ':code_postal' => $data['code_postal'],
            ':id'          => $id,
        ]);
    }
    public function getEmployes(): array
{
    $stmt = $this->db->query('
        SELECT u.*, r.libelle AS role
        FROM utilisateur u
        JOIN role r ON u.role_id = r.role_id
        WHERE u.role_id = 2
        ORDER BY u.created_at DESC
    ');
    return $stmt->fetchAll();
}

public function createEmploye(string $email, string $password): bool
{
    $stmt = $this->db->prepare('
        INSERT INTO utilisateur 
        (role_id, email, `password`, nom, prenom, telephone, adresse, ville, code_postal, pays)
        VALUES (2, :email, :password, "", "", "", "", "", "", "France")
    ');
    return $stmt->execute([
        ':email'    => $email,
        ':password' => password_hash($password, PASSWORD_BCRYPT),
    ]);
}

public function toggleActif(int $id): bool
{
    $stmt = $this->db->prepare('
        UPDATE utilisateur SET actif = NOT actif WHERE utilisateur_id = :id
    ');
    return $stmt->execute([':id' => $id]);
}

public function createPasswordReset(int $userId, string $token, string $expiresAt): bool
{
    $stmt = $this->db->prepare('
        INSERT INTO password_reset (utilisateur_id, token, expires_at)
        VALUES (:user_id, :token, :expires_at)
    ');
    return $stmt->execute([
        ':user_id'    => $userId,
        ':token'      => $token,
        ':expires_at' => $expiresAt,
    ]);
}

public function findValidToken(string $token): array|false
{
    $stmt = $this->db->prepare('
        SELECT * FROM password_reset
        WHERE token = :token
        AND used = 0
        AND expires_at > NOW()
    ');
    $stmt->execute([':token' => $token]);
    return $stmt->fetch();
}

public function updatePassword(int $userId, string $password): bool
{
    $stmt = $this->db->prepare('
        UPDATE utilisateur SET `password` = :password
        WHERE utilisateur_id = :id
    ');
    return $stmt->execute([
        ':password' => password_hash($password, PASSWORD_BCRYPT),
        ':id'       => $userId,
    ]);
}

public function markTokenUsed(int $tokenId): bool
{
    $stmt = $this->db->prepare('
        UPDATE password_reset SET used = 1 WHERE token_id = :id
    ');
    return $stmt->execute([':id' => $tokenId]);
}

}
