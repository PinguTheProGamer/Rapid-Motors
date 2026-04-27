<template>
  <div class="admin-dashboard">
    <nav class="nav admin-nav">
      <div class="nav-container">

        <div class="nav-logo">
          <a href="/" style="text-decoration: none; color: inherit;">
            <div class="logo-text-group">
            <span class="dashboard-logo-rapid">RAPID</span>
            <span class="dashboard-logo-motors">MOTORS</span>
          </div>
          </a>
          <span class="admin-badge">ADMIN</span>
        </div>

        <div class="nav-links">
          <button @click="handleLogout" class="nav-link logout-btn">
            <span class="link-text">Kijelentkezés</span>
            <span class="link-line"></span>
          </button>
        </div>

      </div>
    </nav>

    <div class="dashboard-container">
      <div class="dashboard-header">
        <h1 class="dashboard-title">Autó Adminisztráció</h1>
        <button @click="showAddModal = true" class="add-car-btn">
          + Új autó hozzáadása
        </button>
      </div>

      <div class="cars-table">
        <div class="table-header">
          <div class="header-cell">Kép</div>
          <div class="header-cell">Gyártó</div>
          <div class="header-cell">Modell</div>
          <div class="header-cell">Év</div>
          <div class="header-cell">Kategória</div>
          <div class="header-cell">Ár</div>
          <div class="header-cell">Műveletek</div>
        </div>

        <div v-for="car in cars" :key="car.id" class="table-row">
          <div class="table-cell">
            <img :src="getImagePath(car)" :alt="car.name" class="car-thumbnail" />
          </div>
          <div class="table-cell">{{ car.manufacturer }}</div>
          <div class="table-cell">{{ car.name }}</div>
          <div class="table-cell">{{ car.year }}</div>
          <div class="table-cell">
            <span class="category-badge" :class="car.category">
              {{ huCategoryMap.get(car.category) }}
            </span>
          </div>
          <div class="table-cell">{{ formatPrice(car.price) }}</div>
          <div class="table-cell actions">
            <button @click="editCar(car)" class="edit-btn">✏️</button>
            <button @click="promptDelete(car)" class="delete-btn">🗑️</button>
          </div>
        </div>
      </div>
    </div>

    <div v-if="showAddModal || showEditModal" class="modal-overlay" @click="closeModal">
      <div class="modal" @click.stop>
        <div class="modal-header">
          <h2 class="modal-title">
            {{ showEditModal ? 'Autó szerkesztése' : 'Új autó hozzáadása' }}
          </h2>
          <button @click="closeModal" class="modal-close">✕</button>
        </div>

        <form @submit.prevent="handleSubmit" class="modal-form">
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Gyártó</label>
              <input v-model="formData.manufacturer" type="text" required class="form-input" />
            </div>
            <div class="form-group">
              <label class="form-label">Modell név</label>
              <input v-model="formData.name" type="text" required class="form-input" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Év</label>
              <input v-model.number="formData.year" type="number" required class="form-input" />
            </div>
            <div class="form-group">
              <label class="form-label">Kategória</label>
              <select v-model="formData.category" required class="form-input">
                <option value="supercar">Szuperautó</option>
                <option value="oldtimer">Oldtimer</option>
                <option value="luxury">Luxusautó</option>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Ár (HUF)</label>
              <input v-model.number="formData.price" type="number" step="0.01" required class="form-input" />
            </div>
            <div class="form-group">
              <label class="form-label">Teljesítmény (LE)</label>
              <input v-model.number="formData.horsepower" type="number" required class="form-input" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Motor</label>
              <input v-model="formData.engine" type="text" required class="form-input" />
            </div>
            <div class="form-group">
              <label class="form-label">Végsebesség (km/h)</label>
              <input v-model.number="formData.top_speed" type="number" step="0.1" required class="form-input" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Gyorsulás 0-100 (s)</label>
              <input v-model.number="formData.acceleration" type="number" step="0.01" required class="form-input" />
            </div>
            <div class="form-group featured-group">
              <span class="form-label">Kiemelt autó</span>
              <label class="toggle-wrap" for="is_featured">
                <input
                  type="checkbox"
                  id="is_featured"
                  v-model="formData.is_featured"
                  class="toggle-input"
                />
                <span class="toggle-track">
                  <span class="toggle-thumb" />
                </span>
                <span class="toggle-state-label">{{ formData.is_featured ? 'Igen' : 'Nem' }}</span>
              </label>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Leírás</label>
            <textarea v-model="formData.description" required class="form-textarea" rows="3"></textarea>
          </div>

          <div v-if="error" class="error-message">{{ error }}</div>

          <div class="modal-actions">
            <button type="button" @click="closeModal" class="cancel-btn">Mégse</button>
            <button type="submit" class="submit-btn" :disabled="loading">
              {{ loading ? 'Mentés...' : 'Mentés' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <div v-if="showDeleteModal" class="modal-overlay delete-overlay" @click="cancelDelete">
      <div class="modal delete-modal" @click.stop>
        <div class="delete-icon-wrap">
          <span class="delete-icon">🗑️</span>
        </div>
        <h2 class="delete-title">Autó törlése</h2>
        <p class="delete-body">
          Biztosan törölni szeretnéd a(z)
          <span class="delete-car-name">{{ carToDelete?.manufacturer }} {{ carToDelete?.name }}</span>
          nevű autót? Ez a művelet nem vonható vissza.
        </p>
        <div class="delete-actions">
          <button @click="cancelDelete" class="cancel-btn">Mégse</button>
          <button @click="confirmDelete" class="danger-btn" :disabled="deleteLoading">
            {{ deleteLoading ? 'Törlés...' : 'Törlés' }}
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import axios from 'axios';
import { getImagePath, huCategoryMap } from '../../assets/js/carData';

const router = useRouter();
const authStore = useAuthStore();

const cars = ref([]);
const showAddModal = ref(false);
const showEditModal = ref(false);
const editingCar = ref(null);
const loading = ref(false);
const error = ref('');

const showDeleteModal = ref(false);
const carToDelete = ref(null);
const deleteLoading = ref(false);

const formData = ref({
  name: '',
  manufacturer: '',
  year: new Date().getFullYear(),
  category: 'supercar',
  price: 0,
  horsepower: 0,
  engine: '',
  top_speed: 0,
  acceleration: 0,
  description: '',
  is_featured: false
});

const formatPrice = (price) => {
  return new Intl.NumberFormat('hu-HU', {
    style: 'currency',
    currency: 'HUF',
    minimumFractionDigits: 0,
  }).format(price);
};

const fetchCars = async () => {
  try {
    const response = await axios.get('/api/cars');
    cars.value = response.data;
  } catch (err) {
    console.error('Error fetching cars:', err);
  }
};

const handleLogout = async () => {
  await authStore.logout();
  router.push('/admin/login');
};

const closeModal = () => {
  showAddModal.value = false;
  showEditModal.value = false;
  editingCar.value = null;
  resetForm();
  error.value = '';
};

const resetForm = () => {
  formData.value = {
    name: '',
    manufacturer: '',
    year: new Date().getFullYear(),
    category: 'supercar',
    price: 0,
    horsepower: 0,
    engine: '',
    top_speed: 0,
    acceleration: 0,
    description: '',
    is_featured: false
  };
};

const editCar = (car) => {
  editingCar.value = car;
  formData.value = { ...car };
  showEditModal.value = true;
};

const handleSubmit = async () => {
  loading.value = true;
  error.value = '';

  try {
    if (showEditModal.value && editingCar.value) {
      await axios.put(`/api/cars/${editingCar.value.id}`, formData.value);
    } else {
      await axios.post('/api/cars', formData.value);
    }
    await fetchCars();
    closeModal();
  } catch (err) {
    error.value = err.response?.data?.message || 'Hiba történt';
  } finally {
    loading.value = false;
  }
};

const promptDelete = (car) => {
  carToDelete.value = car;
  showDeleteModal.value = true;
};

const cancelDelete = () => {
  showDeleteModal.value = false;
  carToDelete.value = null;
};

const confirmDelete = async () => {
  if (!carToDelete.value) return;
  deleteLoading.value = true;

  try {
    await axios.delete(`/api/cars/${carToDelete.value.id}`);
    await fetchCars();
    cancelDelete();
  } catch (err) {
    console.error('Error deleting car:', err);
  } finally {
    deleteLoading.value = false;
  }
};

onMounted(() => {
  fetchCars();
});
</script>

<style>
@import '@/assets/css/admin/dashboard.css';
</style>