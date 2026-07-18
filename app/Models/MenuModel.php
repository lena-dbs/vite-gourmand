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

    public function getAllergenes(int $platId): array
    {
        $stmt = $this->db->prepare('
            SELECT a.libelle
            FROM allergene a
            JOIN plat_allergene pa ON a.allergene_id = pa.allergene_id
            WHERE pa.plat_id = :id
        ');
        $stmt->execute([':id' => $platId]);
        return $stmt->fetchAll();
    }

    public function toggleActif(int $id): bool
{
    $stmt = $this->db->prepare('
        UPDATE menu SET actif = NOT actif WHERE menu_id = :id
    ');
    return $stmt->execute([':id' => $id]);
}

}