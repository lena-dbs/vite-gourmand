<?php
declare(strict_types=1);

class MenuModel extends Model
{
    public function getAll(): array
    {
        $stmt = $this->db->query('
            SELECT 
                m.menu_id,
                m.titre,
                m.description,
                m.nb_personnes_min,
                m.prix_base,
                m.stock,
                m.image,
                m.actif,
                t.libelle AS theme,
                r.libelle AS regime
            FROM menu m
            JOIN theme t ON m.theme_id = t.theme_id
            JOIN regime r ON m.regime_id = r.regime_id
            WHERE m.actif = 1
            ORDER BY m.menu_id ASC
        ');
        return $stmt->fetchAll();
    }

    public function getAllAdmin(): array
    {
        $stmt = $this->db->query('
            SELECT
                m.menu_id, m.titre, m.description, m.nb_personnes_min,
                m.prix_base, m.stock, m.image, m.actif,
                t.libelle AS theme, r.libelle AS regime
            FROM menu m
            JOIN theme t ON m.theme_id = t.theme_id
            JOIN regime r ON m.regime_id = r.regime_id
            ORDER BY m.menu_id ASC
        ');
        return $stmt->fetchAll();
    }

    public function getById(int $id): array|false
    {
        $stmt = $this->db->prepare('
            SELECT 
                m.menu_id,
                m.titre,
                m.description,
                m.nb_personnes_min,
                m.prix_base,
                m.stock,
                m.image,
                t.libelle AS theme,
                r.libelle AS regime
            FROM menu m
            JOIN theme t ON m.theme_id = t.theme_id
            JOIN regime r ON m.regime_id = r.regime_id
            WHERE m.menu_id = :id AND m.actif = 1
        ');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function getPlats(int $menuId): array
    {
        $stmt = $this->db->prepare('
            SELECT 
                p.plat_id,
                p.nom,
                p.type,
                p.description,
                p.photo
            FROM plat p
            JOIN menu_plat mp ON p.plat_id = mp.plat_id
            WHERE mp.menu_id = :id
            ORDER BY FIELD(p.type, "entree", "plat", "dessert")
        ');
        $stmt->execute([':id' => $menuId]);
        return $stmt->fetchAll();
    }

    public function getConditions(int $menuId): array
    {
        $stmt = $this->db->prepare('
            SELECT description
            FROM condition_menu
            WHERE menu_id = :id
        ');
        $stmt->execute([':id' => $menuId]);
        return $stmt->fetchAll();
    }

    public function getAllergenesParPlat(array $platIds): array
    {
        if (empty($platIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($platIds), '?'));
        $stmt = $this->db->prepare("
            SELECT pa.plat_id, a.libelle
            FROM allergene a
            JOIN plat_allergene pa ON a.allergene_id = pa.allergene_id
            WHERE pa.plat_id IN ($placeholders)
        ");
        $stmt->execute(array_values($platIds));

        $parPlat = [];
        foreach ($stmt->fetchAll() as $row) {
            $parPlat[$row['plat_id']][] = ['libelle' => $row['libelle']];
        }
        return $parPlat;
    }

    public function toggleActif(int $id): bool
    {
        $stmt = $this->db->prepare('
            UPDATE menu SET actif = NOT actif WHERE menu_id = :id
        ');
        return $stmt->execute([':id' => $id]);
    }

    public function decrementStock(int $id): bool
    {
        $stmt = $this->db->prepare('
            UPDATE menu SET stock = stock - 1 WHERE menu_id = :id AND stock > 0
        ');
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function updateStock(int $id, int $stock): bool
    {
        $stmt = $this->db->prepare('
            UPDATE menu SET stock = :stock WHERE menu_id = :id
        ');
        return $stmt->execute([':stock' => $stock, ':id' => $id]);
    }
}