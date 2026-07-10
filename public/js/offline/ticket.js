/**
 * WMC CAISSE — Ticket de caisse offline
 *
 * Génère et imprime un ticket HTML pour les ventes créées hors connexion.
 */

/**
 * Formater un montant FCFA.
 * @param {number} montant
 * @returns {string}
 */
function formatMontant(montant) {
    return `${Math.round(montant).toLocaleString('fr-FR')} FCFA`;
}

/**
 * Générer le HTML d'un ticket offline.
 * @param {object} vente
 * @returns {string}
 */
export function generateOfflineTicketHtml(vente) {
    const boutique = vente.boutique ?? {};
    const lignes = (vente.produits ?? [])
        .map((p) => {
            const sousTotal = (p.prix_unitaire ?? p.prix_vente ?? 0) * (p.quantite ?? 1);
            return `
                <tr>
                    <td>${p.nom ?? 'Produit'} x${p.quantite ?? 1}</td>
                    <td style="text-align:right">${formatMontant(sousTotal)}</td>
                </tr>`;
        })
        .join('');

    const remise = Number(vente.remise ?? 0);
    const total = (vente.produits ?? []).reduce(
        (sum, p) => sum + (p.prix_unitaire ?? p.prix_vente ?? 0) * (p.quantite ?? 1),
        0
    );
    const totalFinal = total - remise;
    const date = new Date(vente.created_at ?? Date.now());

    return `<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ticket ${vente.numero_local ?? vente.uuid}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 0; padding: 8px; color: #000; }
        .ticket { max-width: 80mm; margin: 0 auto; }
        h1 { font-size: 14px; text-align: center; margin: 0 0 8px; }
        .meta { text-align: center; font-size: 11px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 3px 0; vertical-align: top; }
        .total { font-weight: bold; border-top: 1px dashed #000; padding-top: 6px; margin-top: 6px; }
        .offline-badge { background: #F59E0B; color: #111; text-align: center; padding: 4px; font-weight: bold; margin-top: 10px; font-size: 10px; }
        @media print { body { padding: 0; } }
    </style>
</head>
<body>
    <div class="ticket">
        <h1>${boutique.nom ?? 'WMC Caisse'}</h1>
        <div class="meta">
            Ticket offline : ${vente.numero_local ?? '—'}<br>
            ${date.toLocaleString('fr-FR')}
        </div>
        <table>${lignes}</table>
        ${remise > 0 ? `<div>Remise : -${formatMontant(remise)}</div>` : ''}
        <div class="total">TOTAL : ${formatMontant(totalFinal)}</div>
        <div class="offline-badge">VENTE HORS CONNEXION — SERA SYNCHRONISÉE</div>
    </div>
    <script>window.onload = () => { window.print(); }<\/script>
</body>
</html>`;
}

/**
 * Ouvrir et imprimer un ticket offline.
 * @param {object} vente
 */
export function printOfflineTicket(vente) {
    const html = generateOfflineTicketHtml(vente);
    const win = window.open('', '_blank', 'width=400,height=600');

    if (!win) {
        console.warn('[WMC Offline] Impossible d\'ouvrir la fenêtre d\'impression.');
        return;
    }

    win.document.write(html);
    win.document.close();
}
