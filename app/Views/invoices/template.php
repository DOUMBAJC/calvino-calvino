<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture <?= $sale->invoice_number ?></title>
    <style>
        :root {
            --primary: #2e7d32;
            --primary-dark: #1b5e20;
            --secondary: #66bb6a;
            --accent: #43a047;
            --light-green: #e8f5e9;
            --light-gray: #f5f5f5;
            --dark-gray: #37474f;
            --text: #37474f;
            --light-text: #78909c;
            --border: #e0e0e0;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', 'Roboto', 'Helvetica Neue', sans-serif;
            color: var(--text);
            background-color: #f9f9f9;
            line-height: 1.4;
            padding: 10px;
        }
        .invoice-container {
            max-width: 850px;
            margin: 10px auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.08);
            padding: 20px;
            position: relative;
            overflow: hidden;
        }
        .invoice-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(to right, var(--primary), var(--secondary));
        }
        .invoice-header {
            display: flex;
            justify-content: space-between;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border);
        }
        .brand {
            display: flex;
            flex-direction: column;
        }
        .brand-logo {
            display: flex;
            align-items: center;
            font-size: 24px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 5px;
        }
        .logo-icon {
            margin-right: 8px;
            background-color: var(--primary);
            color: white;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .brand-tagline {
            color: var(--secondary);
            font-size: 12px;
            font-style: italic;
        }
        .invoice-info {
            text-align: right;
        }
        .invoice-id {
            font-size: 20px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 3px;
        }
        .invoice-date {
            color: var(--light-text);
            font-size: 13px;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 5px;
        }
        .badge-success {
            background-color: rgba(46, 125, 50, 0.1);
            color: var(--primary);
        }
        .badge-warning {
            background-color: rgba(255, 152, 0, 0.1);
            color: #ff9800;
        }
        .badge-danger {
            background-color: rgba(244, 67, 54, 0.1);
            color: #f44336;
        }
        .section {
            margin: 15px 0;
        }
        .section-title {
            font-size: 14px;
            font-weight: 600;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
            padding-bottom: 3px;
            border-bottom: 2px solid var(--light-green);
        }
        .client-info, .payment-info {
            display: flex;
            justify-content: space-between;
        }
        .client-details, .payment-details {
            width: 48%;
            background-color: var(--light-green);
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }
        .client-name {
            font-weight: 600;
            font-size: 16px;
            margin-bottom: 5px;
            color: var(--primary);
        }
        .info-item {
            display: flex;
            margin-bottom: 4px;
        }
        .info-label {
            width: 80px;
            color: var(--primary);
            font-weight: 500;
            font-size: 13px;
        }
        .info-value {
            flex: 1;
            color: var(--text);
            font-size: 13px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }
        .items-table th {
            background-color: var(--primary);
            color: white;
            font-weight: 600;
            text-align: left;
            padding: 10px;
            border-bottom: 2px solid var(--primary-dark);
            font-size: 13px;
        }
        .items-table td {
            padding: 8px 10px;
            border-bottom: 1px solid var(--border);
            color: var(--text);
            font-size: 13px;
        }
        .items-table tr:last-child td {
            border-bottom: none;
        }
        .items-table tr:nth-child(even) {
            background-color: var(--light-green);
        }
        .text-right {
            text-align: right;
        }
        .quantity {
            text-align: center;
        }
        .total-section {
            margin-top: 15px;
            display: flex;
            justify-content: flex-end;
        }
        .total-table {
            width: 280px;
            border-top: 2px solid var(--border);
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
        }
        .total-label {
            font-weight: 500;
            color: var(--primary);
            font-size: 13px;
        }
        .total-value {
            font-weight: 500;
            color: var(--text);
            font-size: 13px;
        }
        .grand-total {
            padding: 8px;
            margin-top: 5px;
            border-top: 2px solid var(--primary);
            background-color: var(--light-green);
            border-radius: 5px;
        }
        .grand-total .total-label {
            font-weight: 700;
            color: var(--primary-dark);
            font-size: 15px;
        }
        .grand-total .total-value {
            font-weight: 700;
            color: var(--primary-dark);
            font-size: 15px;
        }
        .payment-note {
            margin-top: 15px;
            padding: 10px;
            background-color: var(--light-green);
            border-radius: 8px;
            border-left: 4px solid var(--primary);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }
        .payment-note h3 {
            font-size: 14px;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 5px;
        }
        .payment-note p {
            color: var(--text);
            margin-bottom: 3px;
            font-size: 13px;
        }
        .footer {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px solid var(--border);
            text-align: center;
        }
        .footer-thanks {
            font-size: 16px;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 5px;
        }
        .footer-info {
            color: var(--light-text);
            font-size: 12px;
            margin-bottom: 3px;
        }
        .footer-contact {
            margin-top: 8px;
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
        }
        .contact-item {
            display: inline-flex;
            align-items: center;
            margin: 0 10px;
            color: var(--text);
            font-size: 12px;
        }
        .contact-icon {
            margin-right: 5px;
            color: var(--primary);
            font-size: 14px;
        }
        .divider {
            height: 2px;
            background: linear-gradient(to right, var(--primary), var(--secondary));
            margin: 15px 0;
            border-radius: 2px;
            opacity: 0.3;
        }
        .print-button {
            display: block;
            width: 180px;
            margin: 15px auto;
            padding: 10px 15px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-align: center;
            transition: background-color 0.3s;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .print-button:hover {
            background-color: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.15);
        }
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 100px;
            opacity: 0.03;
            color: var(--primary);
            font-weight: 700;
            pointer-events: none;
            z-index: 0;
            white-space: nowrap;
        }
        @media print {
            body {
                background-color: white;
                padding: 0;
                margin: 0;
            }
            .invoice-container {
                box-shadow: none;
                max-width: 100%;
                padding: 10px;
                margin: 0;
            }
            .print-button {
                display: none;
            }
            @page {
                margin: 0.3cm;
                size: A4;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="watermark">PHARMACIE HEALTH+</div>
        <div class="invoice-header">
            <div class="brand">
                <div class="brand-logo">
                    <div class="logo-icon">💊</div>
                    HEALTH+
                </div>
                <div class="brand-tagline">Votre santé, notre priorité</div>
            </div>
            <div class="invoice-info">
                <div class="invoice-id">FACTURE #<?= $sale->invoice_number ?></div>
                <div class="invoice-date">Date: <?= $date ?></div>
                <div class="badge <?= $sale->payment_status === 'paid' ? 'badge-success' : ($sale->payment_status === 'pending' ? 'badge-warning' : 'badge-danger') ?>">
                    <?= ucfirst($sale->payment_status) ?>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="client-info">
                <div class="client-details">
                    <div class="section-title">Informations Client</div>
                    <div class="client-name"><?= $customer->name ?? 'Client non spécifié' ?></div>
                    <div class="info-item">
                        <div class="info-label">Email:</div>
                        <div class="info-value"><?= $customer->email ?? 'Non renseigné' ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Téléphone:</div>
                        <div class="info-value"><?= $customer->phone ?? 'Non renseigné' ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Adresse:</div>
                        <div class="info-value"><?= $customer->address ?? 'Non renseignée' ?></div>
                    </div>
                </div>
                
                <div class="payment-details">
                    <div class="section-title">Détails de paiement</div>
                    <div class="info-item">
                        <div class="info-label">Mode:</div>
                        <div class="info-value"><?= ucfirst($sale->payment_method) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Statut:</div>
                        <div class="info-value"><?= ucfirst($sale->payment_status) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Date:</div>
                        <div class="info-value"><?= $date ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="divider"></div>
        
        <div class="section">
            <div class="section-title">Détails de la commande</div>
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 40%;">Produit</th>
                        <th style="width: 15%;" class="quantity">Quantité</th>
                        <th style="width: 20%;" class="text-right">Prix unitaire</th>
                        <th style="width: 25%;" class="text-right">Montant</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?= $product->name ?></td>
                        <td class="quantity"><?= isset($product->pivot) ? $product->pivot->quantity : 'N/A' ?></td>
                        <td class="text-right"><?= isset($product->pivot) ? number_format($product->pivot->unit_price ?? 0, 0, ',', ' ') : 0 ?> FCFA</td>
                        <td class="text-right"><?= isset($product->pivot) ? number_format($product->pivot->total_price ?? 0, 0, ',', ' ') : 0 ?> FCFA</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="total-section">
                <div class="total-table">
                    <div class="total-row">
                        <div class="total-label">Sous-total</div>
                        <div class="total-value"><?= number_format($sale->total_amount, 0, ',', ' ') ?> FCFA</div>
                    </div>

                    <div class="total-row">
                        <div class="total-label">TVA (0%)</div>
                        <div class="total-value"><?= number_format($sale->discount, 0, ',', ' ') ?> FCFA</div>
                    </div>
                    <div class="total-row grand-total">
                        <div class="total-label">Total</div>
                        <div class="total-value"><?= number_format($sale->total_amount, 0, ',', ' ') ?> FCFA</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="payment-note">
            <h3>Informations de paiement</h3>
            <p>Merci pour votre achat. Votre paiement a été <?= $sale->payment_status === 'paid' ? 'reçu' : 'enregistré' ?>.</p>
            <?php if($sale->payment_status !== 'paid'): ?>
            <p>Veuillez effectuer votre paiement dès que possible pour finaliser votre commande.</p>
            <?php endif; ?>
        </div>

        <div class="footer">
            <div class="footer-thanks">Merci pour votre confiance!</div>
            <div class="footer-info">Pharmacie HEALTH+ | Quartier Bini-Dang, Ngaoundere, Cameroun</div>
            <div class="footer-contact">
                <div class="contact-item">
                    <span class="contact-icon">📞</span> (+237) 699 999 999
                </div>
                <div class="contact-item">
                    <span class="contact-icon">✉️</span> contact@pharmasys.cm
                </div>
            </div>
            <div class="footer-info" style="margin-top: 5px; font-size: 11px;">
                RC: XXXX/XX/XX | NIU: PXXXXXXXX | N° Contribuable: MXXXXXXXX
            </div>
        </div>
    </div>
    
    <button class="print-button" onclick="window.print()">Imprimer la facture</button>
</body>
</html> 
