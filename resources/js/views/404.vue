<template>
  <div class="not-found">
    <div class="content">
      <div class="error-code">404</div>
      <h1 class="error-title">Az oldal nem található</h1>
      <p class="error-sub">A keresett oldal nem létezik vagy el lett távolítva.</p>
      <router-link to="/" class="error-home-btn">← Vissza a főoldalra</router-link>
    </div>
  </div>
</template>

<script setup>
</script>

<style>
@import '@/assets/css/404.css';
</style>