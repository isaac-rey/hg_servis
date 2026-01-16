<?php
// Incluir el helper de imágenes (asegúrate de que la ruta sea correcta)
require_once 'helpers/image_helper.php';
/* Evitar errores si no existe */
$featured_products = $featured_products ?? [];

/* Función respaldo */
if (!function_exists('formatPrice')) {
    function formatPrice($price)
    {
        return 'Gs. ' . number_format((float)$price, 0, ',', '.');
    }
}
?>

<section class="hero">
    <div class="container">
        <div class="hero-content">
            <h1>📱 Reparación Rápida y Garantizada</h1>
            <p>Expertos en reparación de celulares con repuestos 100% originales y servicio profesional</p>
            <div class="hero-stats">
                <div class="stat-item">
                    <span class="stat-number">24-48</span>
                    <span class="stat-label">Horas de servicio</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">6</span>
                    <span class="stat-label">Meses de garantía</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">+1000</span>
                    <span class="stat-label">Celulares reparados</span>
                </div>
            </div>
            <div class="hero-actions">
                <a href="contact.php" class="btn btn-primary pulse">📞 Cotizar Reparación</a>
                <a href="#services" class="btn btn-secondary">Ver Servicios</a>
            </div>
        </div>
        <div class="hero-image">
            <img src="assets/images/hero-repair.png" alt="Reparación de celulares" loading="lazy">
        </div>
    </div>
</section>

<section class="urgent-banner">
    <div class="container">
        <div class="urgent-content">
            <span class="urgent-icon">🚨</span>
            <div class="urgent-text">
                <h3>¡Reparación de Emergencia!</h3>
                <p>Pantalla rota, no carga, se apaga solo. Servicio express disponible.</p>
            </div>
            <a href="tel:+595 974 717490" class="btn btn-danger">📞 Llamar Ahora</a>
        </div>
    </div>
</section>

<section id="services" class="services">
    <div class="container">
        <h2>Nuestros Servicios de Reparación</h2>
        <p class="section-subtitle">Soluciones técnicas para todas las marcas y modelos</p>

        <div class="services-grid">
            <div class="service-card">
                <div class="service-icon">📱</div>
                <h3>Cambio de Pantalla</h3>
                <p class="service-description">Reparamos pantallas rotas de cualquier modelo con repuestos originales</p>
                <div class="service-details">
                    <span class="service-time">⏱️ 1-2 horas</span>
                    <span class="service-warranty">✅ 6 meses garantía</span>
                </div>
                <div class="service-price">Desde $50</div>
                <a href="service.php?id=1" class="btn btn-outline">Más Información</a>
            </div>

            <div class="service-card">
                <div class="service-icon">⚡</div>
                <h3>Cambio de Batería</h3>
                <p class="service-description">Baterías originales para recuperar la autonomía de tu dispositivo</p>
                <div class="service-details">
                    <span class="service-time">⏱️ 45 minutos</span>
                    <span class="service-warranty">✅ 12 meses garantía</span>
                </div>
                <div class="service-price">Desde $30</div>
                <a href="service.php?id=2" class="btn btn-outline">Más Información</a>
            </div>

            <div class="service-card">
                <div class="service-icon">🔌</div>
                <h3>Reparación de Puerto</h3>
                <p class="service-description">Solución para problemas de carga, auriculares y conexión USB</p>
                <div class="service-details">
                    <span class="service-time">⏱️ 1 hora</span>
                    <span class="service-warranty">✅ 3 meses garantía</span>
                </div>
                <div class="service-price">Desde $25</div>
                <a href="service.php?id=3" class="btn btn-outline">Más Información</a>
            </div>

            <div class="service-card">
                <div class="service-icon">🔄</div>
                <h3>Sistema y Software</h3>
                <p class="service-description">Actualizaciones, liberaciones, desbloqueos y recuperación de datos</p>
                <div class="service-details">
                    <span class="service-time">⏱️ 30-60 minutos</span>
                    <span class="service-warranty">✅ 1 mes garantía</span>
                </div>
                <div class="service-price">Desde $20</div>
                <a href="service.php?id=4" class="btn btn-outline">Más Información</a>
            </div>
        </div>
    </div>
</section>

<section class="features">
    <div class="container">
        <h2>¿Por qué elegirnos?</h2>
        <div class="features-grid">
            <div class="feature-item">
                <div class="feature-icon">🔧</div>
                <h3>Técnicos Certificados</h3>
                <p>Personal capacitado con certificación oficial de las principales marcas</p>
            </div>
            <div class="feature-item">
                <div class="feature-icon">✅</div>
                <h3>Repuestos Originales</h3>
                <p>Usamos solo componentes de calidad con garantía incluida</p>
            </div>
            <div class="feature-item">
                <div class="feature-icon">⚡</div>
                <h3>Servicio Express</h3>
                <p>Reparaciones urgentes en el mismo día para problemas críticos</p>
            </div>
            <div class="feature-item">
                <div class="feature-icon">💰</div>
                <h3>Precios Transparentes</h3>
                <p>Cotización sin costo y sin sorpresas en el precio final</p>
            </div>
        </div>
    </div>
</section>

<section id="featured" class="featured-products">
    <div class="container">
        <h2>Accesorios Destacados</h2>
        <p class="section-subtitle">Complementos y repuestos para tu dispositivo</p>

        <div class="products-grid">
            <?php if (empty($featured_products)): ?>
                <div class="no-products">
                    <p>No hay productos destacados disponibles.</p>
                    <a href="products.php" class="btn btn-primary">Ver todos los accesorios</a>
                </div>
            <?php else: ?>
                <?php foreach ($featured_products as $product): ?>
                    <?php
                    $stock = $product['stock'] ?? $product['cantidad'] ?? 0;
                    $original = !empty($product['original']);
                    $garantia = $product['garantia'] ?? null;
                    $calidad = $product['calidad'] ?? '';
                    $compatibility = array_filter(array_map('trim', explode(',', $calidad)));
                    ?>
                    <div class="product-card">
                        <div class="product-image">
                            <img src="<?= htmlspecialchars(getProductImage($product)) ?>"
                                alt="<?= htmlspecialchars($product['imagen'] ?? 'Producto') ?>"
                                loading="lazy">

                            <div class="product-badges">
                                <?php if ($original): ?>
                                    <span class="badge original">✅ Original</span>
                                <?php endif; ?>
                                <?php if ($garantia): ?>
                                    <span class="badge warranty">🛡️ <?= (int)$garantia ?> meses</span>
                                <?php endif; ?>
                                <?php if ($stock > 0 && $stock < 10): ?>
                                    <span class="badge low-stock">🚀 ¡Últimas unidades!</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="product-info">
                            <h3><?= htmlspecialchars($product['marca'] ?? '') ?></h3>

                            <p class="product-compatibility">
                                📱 <?= implode(', ', array_slice($compatibility, 0, 2)) ?>
                                <?= count($compatibility) > 2 ? '...' : '' ?>
                            </p>

                            <p class="product-category"><?= htmlspecialchars($product['categoria'] ?? '') ?></p>

                            <div class="product-features">
                                <?php if (!empty($product['modelo'])): ?>
                                    <span>🔧 <?= htmlspecialchars($product['modelo']) ?></span>
                                <?php endif; ?>

                                <?php if ($stock > 0): ?>
                                    <span class="stock-available">✅ En stock</span>
                                <?php else: ?>
                                    <span class="stock-out">⏳ Agotado</span>
                                <?php endif; ?>
                            </div>
                            
                             <!-- Precio en guaraníes -->
                            <div class="product-price">
                            <span class="current-price">
                                ₲<?php echo number_format($product['precio'], 0, ',', '.'); ?>
                            </span>
                        </div>


                            <div class="product-actions">
                                <?php if (!empty($_SESSION['user_id'])): ?>
                                    <form method="POST" action="add-to-cart.php">
                                        <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
                                        <input type="number" name="quantity" value="1" min="1" max="<?= min($stock, 10) ?>">
                                        <button type="submit" class="btn btn-cart" <?= $stock == 0 ? 'disabled' : '' ?>>
                                            <?= $stock == 0 ? 'Agotado' : 'Agregar' ?>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <p>🔐 <a href="login.php">Inicia sesión</a> para comprar</p>
                                <?php endif; ?>

                                <a href="product-detail.php?id=<?= (int)$product['id'] ?>" class="btn btn-details">
                                    Ver Especificaciones
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>


<section class="brands">
    <div class="container">
        <h2>Marcas que reparamos</h2>
        <div class="brands-grid">
            <div class="brand-item">🍎 Apple</div>
            <div class="brand-item">🤖 Samsung</div>
            <div class="brand-item">📱 Xiaomi</div>
            <div class="brand-item">🔵 Huawei</div>
            <div class="brand-item">🟢 Motorola</div>
            <div class="brand-item">🔴 LG</div>
            <div class="brand-item">⚫ Sony</div>
            <div class="brand-item">🟣 Nokia</div>
        </div>
    </div>
</section>

<section class="testimonials">
    <div class="container">
        <h2>Lo que dicen nuestros clientes</h2>
        <div class="testimonials-grid">
            <div class="testimonial-card">
                <div class="testimonial-content">
                    <p>"Excelente servicio, repararon mi iPhone en menos de 2 horas. Muy profesionales y con garantía."</p>
                </div>
                <div class="testimonial-author">
                    <div class="author-info">
                        <h4>María González</h4>
                        <span class="author-device">📱 iPhone 13 Pro</span>
                    </div>
                </div>
            </div>

            <div class="testimonial-card">
                <div class="testimonial-content">
                    <p>"Cambiaron la pantalla de mi Samsung y quedó como nuevo. Precio justo y atención personalizada."</p>
                </div>
                <div class="testimonial-author">
                    <div class="author-info">
                        <h4>Carlos Martínez</h4>
                        <span class="author-device">📱 Samsung Galaxy S22</span>
                    </div>
                </div>
            </div>

            <div class="testimonial-card">
                <div class="testimonial-content">
                    <p>"Tenía problemas con la carga de mi Xiaomi, lo diagnosticaron rápido y lo repararon el mismo día."</p>
                </div>
                <div class="testimonial-author">
                    <div class="author-info">
                        <h4>Ana López</h4>
                        <span class="author-device">📱 Xiaomi Redmi Note 11</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>