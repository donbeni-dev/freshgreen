// =====================
// DATA
// =====================
const USERS = {
  'admin@freshgreen.com': { password: 'admin123', role: 'admin', name: 'Admin' },
  'user@freshgreen.com':  { password: 'user123',  role: 'user',  name: 'Jane' }
};

let currentRole = 'user';
let cart = [];
let imgData = null;
let activeFilter = 'All';

let products = [
  { id: 1, name: 'Sweet Mangoes',     price: 180, category: 'Fruits',     desc: 'Farm-fresh mangoes sourced from Meru county — sweet, juicy, and ripe.', emoji: '🥭', img: null },
  { id: 2, name: 'Sukuma Wiki',        price: 50,  category: 'Vegetables', desc: 'Locally grown kale, freshly harvested every morning.', emoji: '🥬', img: null },
  { id: 3, name: 'Organic Tomatoes',   price: 120, category: 'Organic',    desc: 'Pesticide-free tomatoes from certified organic farms in Limuru.', emoji: '🍅', img: null },
  { id: 4, name: 'Passion Fruits',     price: 200, category: 'Fruits',     desc: 'Tart and aromatic passion fruits perfect for juicing.', emoji: '🟡', img: null },
  { id: 5, name: 'Spinach Bunch',      price: 40,  category: 'Vegetables', desc: 'Tender baby spinach, hand-picked and cleaned.', emoji: '🌿', img: null },
  { id: 6, name: 'Fresh Avocados',     price: 90,  category: 'Fruits',     desc: "Creamy Hass avocados from Murang'a — ready to eat.", emoji: '🥑', img: null },
  { id: 7, name: 'Coriander Herbs',    price: 30,  category: 'Herbs',      desc: 'Fresh bunches of coriander, great for seasoning.', emoji: '🌱', img: null },
  { id: 8, name: 'Bulk Onions',        price: 350, category: 'Bulk Supply',desc: '5kg sack of premium red onions for homes and restaurants.', emoji: '🧅', img: null }
];

// =====================
// LOGIN
// =====================
function selectRole(role) {
  currentRole = role;
  document.getElementById('tab-user').classList.toggle('active', role === 'user');
  document.getElementById('tab-admin').classList.toggle('active', role === 'admin');
  document.getElementById('login-email').value = role === 'admin' ? 'admin@freshgreen.com' : 'user@freshgreen.com';
  document.getElementById('login-password').value = role === 'admin' ? 'admin123' : 'user123';
}

function doLogin() {
  const email = document.getElementById('login-email').value.trim();
  const pass  = document.getElementById('login-password').value;
  const errEl = document.getElementById('login-error');
  const u = USERS[email];
  if (!u || u.password !== pass) {
    errEl.textContent = '❌ Invalid email or password.';
    errEl.classList.remove('hidden');
    return;
  }
  errEl.classList.add('hidden');
  currentRole = u.role;
  document.getElementById('login-page').classList.add('hidden');
  document.getElementById('app').classList.remove('hidden');
  document.getElementById('role-badge').textContent = u.role === 'admin' ? '🛠 Admin' : '👤 Customer';
  document.getElementById('role-badge').className = 'nav-badge' + (u.role === 'admin' ? ' admin' : '');
  document.getElementById('user-avatar').textContent = u.name[0];
  const tabs = document.getElementById('nav-tabs');
  tabs.innerHTML = `<div class="nav-tab active" onclick="showSection('shop')">🛒 Shop</div>`;
  if (u.role === 'admin') {
    tabs.innerHTML += `<div class="nav-tab" onclick="showSection('admin')">🛠 Admin Panel</div>`;
    document.getElementById('cart-float').classList.add('hidden');
  } else {
    document.getElementById('cart-float').classList.remove('hidden');
  }
  showSection('shop');
}

function logout() {
  document.getElementById('login-page').classList.remove('hidden');
  document.getElementById('app').classList.add('hidden');
  cart = [];
  updateCartCount();
}

// =====================
// NAVIGATION
// =====================
function showSection(sec) {
  document.getElementById('section-shop').classList.toggle('hidden', sec !== 'shop');
  document.getElementById('section-admin').classList.toggle('hidden', sec !== 'admin');
  document.querySelectorAll('.nav-tab').forEach(t => {
    t.classList.toggle('active',
      (sec === 'shop' && t.textContent.includes('Shop')) ||
      (sec === 'admin' && t.textContent.includes('Admin'))
    );
  });
  if (sec === 'shop') { renderProducts(); renderFilterTabs(); }
  if (sec === 'admin') { adminSection('dashboard'); }
  document.getElementById('cart-float').classList.toggle('hidden', sec === 'admin' || currentRole === 'admin');
}

function adminSection(sub) {
  document.getElementById('admin-dashboard').classList.toggle('hidden', sub !== 'dashboard');
  document.getElementById('admin-add-product').classList.toggle('hidden', sub !== 'add-product');
  document.getElementById('admin-manage-products').classList.toggle('hidden', sub !== 'manage-products');
  document.querySelectorAll('.sidebar-item').forEach(el => {
    const txt = el.textContent.trim();
    el.classList.toggle('active',
      (sub === 'dashboard' && txt.includes('Dashboard')) ||
      (sub === 'add-product' && txt.includes('Add Product')) ||
      (sub === 'manage-products' && txt.includes('Manage'))
    );
  });
  if (sub === 'dashboard') renderAdminStats();
  if (sub === 'manage-products') renderAdminProducts();
}

// =====================
// SHOP
// =====================
function getCategories() {
  return ['All', ...new Set(products.map(p => p.category))];
}

function renderFilterTabs() {
  const el = document.getElementById('filter-tabs');
  el.innerHTML = getCategories().map(c =>
    `<div class="filter-tab${c === activeFilter ? ' active' : ''}" onclick="setFilter('${c}')">${c}</div>`
  ).join('');
}

function setFilter(f) {
  activeFilter = f;
  renderFilterTabs();
  renderProducts();
}

function renderProducts() {
  const query = (document.getElementById('search-input')?.value || '').toLowerCase();
  const filtered = products.filter(p =>
    (activeFilter === 'All' || p.category === activeFilter) &&
    (p.name.toLowerCase().includes(query) || p.desc.toLowerCase().includes(query))
  );
  document.getElementById('stat-count').textContent = products.length;
  const grid = document.getElementById('product-grid');
  if (!filtered.length) {
    grid.innerHTML = `<div class="empty-state"><div class="empty-icon">🌾</div>No products found. Try a different search.</div>`;
    return;
  }
  grid.innerHTML = filtered.map(p => `
    <div class="product-card">
      <div class="product-badge">${p.category}</div>
      <div class="product-img">${p.img ? `<img src="${p.img}" style="width:100%;height:180px;object-fit:cover">` : p.emoji || '🥬'}</div>
      <div class="product-info">
        <div class="product-name">${p.name}</div>
        <div class="product-desc">${p.desc}</div>
        <div class="product-footer">
          <div class="product-price">KSh ${p.price}</div>
          <button class="add-cart-btn" onclick="addToCart(${p.id})">+</button>
        </div>
      </div>
    </div>
  `).join('');
}

// =====================
// CART
// =====================
function addToCart(id) {
  const p = products.find(x => x.id === id);
  if (!p) return;
  const existing = cart.find(c => c.id === id);
  if (existing) existing.qty++;
  else cart.push({ ...p, qty: 1 });
  updateCartCount();
  showToast(`✅ ${p.name} added to cart!`);
}

function updateCartCount() {
  const total = cart.reduce((s, i) => s + i.qty, 0);
  document.getElementById('cart-count').textContent = total;
  renderCartItems();
}

function toggleCart() {
  document.getElementById('cart-panel').classList.toggle('open');
}

function renderCartItems() {
  const el = document.getElementById('cart-items');
  if (!cart.length) {
    el.innerHTML = `<div class="cart-empty"><div style="font-size:48px">🛒</div><p>Your cart is empty</p></div>`;
  } else {
    el.innerHTML = cart.map(item => `
      <div class="cart-item">
        <div class="cart-item-icon">${item.emoji || '🥬'}</div>
        <div class="cart-item-info">
          <div class="name">${item.name} ×${item.qty}</div>
          <div class="price">KSh ${item.price * item.qty}</div>
        </div>
        <button class="cart-item-remove" onclick="removeFromCart(${item.id})">✕</button>
      </div>
    `).join('');
  }
  const subtotal = cart.reduce((s, i) => s + i.price * i.qty, 0);
  document.getElementById('cart-subtotal').textContent = `KSh ${subtotal}`;
  document.getElementById('cart-total-val').textContent = `KSh ${subtotal + (cart.length ? 100 : 0)}`;
}

function removeFromCart(id) {
  cart = cart.filter(c => c.id !== id);
  updateCartCount();
}

function checkout() {
  if (!cart.length) return;
  cart = [];
  updateCartCount();
  document.getElementById('cart-panel').classList.remove('open');
  showToast('🎉 Order placed! Your produce is on the way.');
}

// =====================
// ADMIN
// =====================
function renderAdminStats() {
  const el = document.getElementById('admin-stats');
  const totalVal = products.reduce((s, p) => s + p.price, 0);
  const cats = new Set(products.map(p => p.category)).size;
  el.innerHTML = `
    <div class="stat-card"><div class="icon">📦</div><div class="num">${products.length}</div><div class="lbl">Total Products</div></div>
    <div class="stat-card"><div class="icon">🏷️</div><div class="num">${cats}</div><div class="lbl">Categories</div></div>
    <div class="stat-card"><div class="icon">🌾</div><div class="num">500+</div><div class="lbl">Partner Farmers</div></div>
    <div class="stat-card"><div class="icon">💰</div><div class="num">KSh ${totalVal.toLocaleString()}</div><div class="lbl">Inventory Value</div></div>
  `;
}

function previewImage(input) {
  const file = input.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = e => {
    imgData = e.target.result;
    const prev = document.getElementById('p-img-preview');
    prev.src = imgData;
    prev.classList.remove('hidden');
  };
  reader.readAsDataURL(file);
}

function addProduct() {
  const name  = document.getElementById('p-name').value.trim();
  const price = parseFloat(document.getElementById('p-price').value);
  const cat   = document.getElementById('p-category').value;
  const desc  = document.getElementById('p-desc').value.trim();
  const emoji = document.getElementById('p-emoji').value.trim() || '🥬';
  if (!name || !price || !cat || !desc) {
    showToast('⚠️ Please fill in all required fields.', true);
    return;
  }
  products.push({ id: Date.now(), name, price, category: cat, desc, emoji, img: imgData });
  clearForm();
  showToast(`✅ "${name}" added to the store!`);
  renderFilterTabs();
}

function clearForm() {
  ['p-name', 'p-price', 'p-desc', 'p-emoji'].forEach(id => document.getElementById(id).value = '');
  document.getElementById('p-category').value = '';
  document.getElementById('p-img-preview').classList.add('hidden');
  imgData = null;
}

function renderAdminProducts() {
  const el = document.getElementById('admin-product-list');
  if (!products.length) {
    el.innerHTML = `<div class="empty-state"><div class="empty-icon">📦</div>No products yet.</div>`;
    return;
  }
  el.innerHTML = products.map(p => `
    <div class="admin-product-item">
      <div class="admin-product-thumb">${p.img ? `<img src="${p.img}" style="width:64px;height:64px;object-fit:cover;border-radius:10px">` : p.emoji}</div>
      <div class="admin-product-info">
        <div class="name">${p.name}</div>
        <div class="meta">${p.category} • KSh ${p.price}</div>
        <div class="meta" style="margin-top:4px;font-size:12px">${p.desc.substring(0,80)}…</div>
      </div>
      <div class="admin-product-actions">
        <button class="btn btn-outline btn-sm" onclick="editProduct(${p.id})">✏️ Edit</button>
        <button class="btn btn-danger btn-sm" onclick="confirmDelete(${p.id})">🗑 Delete</button>
      </div>
    </div>
  `).join('');
}

function editProduct(id) {
  const p = products.find(x => x.id === id);
  if (!p) return;
  adminSection('add-product');
  document.getElementById('p-name').value = p.name;
  document.getElementById('p-price').value = p.price;
  document.getElementById('p-category').value = p.category;
  document.getElementById('p-desc').value = p.desc;
  document.getElementById('p-emoji').value = p.emoji || '';
  if (p.img) {
    imgData = p.img;
    const prev = document.getElementById('p-img-preview');
    prev.src = p.img;
    prev.classList.remove('hidden');
  }
  products = products.filter(x => x.id !== id);
  showToast('✏️ Editing product — save with Add Product.');
}

function confirmDelete(id) {
  document.getElementById('confirm-modal').classList.remove('hidden');
  document.getElementById('confirm-delete-btn').onclick = () => deleteProduct(id);
}

function deleteProduct(id) {
  products = products.filter(p => p.id !== id);
  closeModal();
  renderAdminProducts();
  showToast('🗑 Product deleted.');
}

function closeModal() {
  document.getElementById('confirm-modal').classList.add('hidden');
}

// =====================
// TOAST
// =====================
function showToast(msg, warn = false) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.style.background = warn ? 'var(--warning)' : 'var(--dark-green)';
  t.classList.remove('hidden');
  setTimeout(() => t.classList.add('hidden'), 3000);
}

// =====================
// INIT
// =====================
renderProducts();
renderFilterTabs();