<template>
  <a 
    href="#"
    :class="[
      'relative flex items-center w-full h-12 px-3 mt-2 rounded transition-all duration-200 group',
      isCollapsed ? 'justify-center' : '',
      isActive ? activeClasses[theme] : hoverClasses[theme]
    ]"
    :title="isCollapsed ? item.label : ''"
  >
    <component :is="item.icon" class="w-6 h-6 flex-shrink-0 stroke-[2px]" />
    <span v-if="!isCollapsed" class="ml-3 text-sm font-medium whitespace-nowrap">
      {{ item.label }}
    </span>
    
    <span 
      v-if="item.hasNotification" 
      class="absolute top-2 left-2 w-2 h-2 bg-indigo-500 rounded-full border border-current"
      :class="isCollapsed ? 'ml-0' : 'ml-1'"
    ></span>
  </a>
</template>

<script setup>
defineProps(['item', 'isCollapsed', 'theme', 'isActive']);

const activeClasses = {
  dark: 'bg-gray-700 text-gray-100',
  light: 'bg-gray-300 text-gray-900',
  indigo: 'bg-indigo-700 text-white'
};

const hoverClasses = {
  dark: 'hover:bg-gray-700 hover:text-gray-300',
  light: 'hover:bg-gray-300 hover:text-gray-900',
  indigo: 'hover:bg-indigo-700 hover:text-indigo-100'
};
</script>