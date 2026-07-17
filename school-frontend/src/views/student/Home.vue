<!-- <template>
  <StudentLayout>
    <div class="space-y-8">
      <div class="bg-indigo-600 rounded-3xl p-8 text-white shadow-xl shadow-indigo-200 flex justify-between items-center relative overflow-hidden">
        <div class="z-10">
          <h2 class="text-3xl font-black mb-2">សួស្តី សំណាង!</h2>
          <p class="text-indigo-100">តើអ្នកត្រៀមខ្លួនសម្រាប់ការសិក្សាថ្ងៃនេះហើយឬនៅ?</p>
          <button class="mt-4 bg-white text-indigo-600 px-6 py-2 rounded-full font-bold text-sm hover:bg-indigo-50 transition-all">
            មើលកាលវិភាគរៀន
          </button>
        </div>
        <div class="hidden lg:block opacity-20">
          <BookOpenIcon class="w-32 h-32" />
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm flex items-center gap-4">
          <div class="p-3 bg-green-50 text-green-600 rounded-2xl">
            <ClipboardListIcon class="w-6 h-6" />
          </div>
          <div>
            <p class="text-xs font-bold text-slate-400 uppercase">វត្តមានសរុប</p>
            <h3 class="text-xl font-black text-slate-900">98%</h3>
          </div>
        </div>
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm flex items-center gap-4">
          <div class="p-3 bg-orange-50 text-orange-600 rounded-2xl">
            <AwardIcon class="w-6 h-6" />
          </div>
          <div>
            <p class="text-xs font-bold text-slate-400 uppercase">ចំណាត់ថ្នាក់ខែមុន</p>
            <h3 class="text-xl font-black text-slate-900">លេខ ០៣</h3>
          </div>
        </div>
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm flex items-center gap-4">
          <div class="p-3 bg-purple-50 text-purple-600 rounded-2xl">
            <BookOpenIcon class="w-6 h-6" />
          </div>
          <div>
            <p class="text-xs font-bold text-slate-400 uppercase">កិច្ចការផ្ទះនៅសល់</p>
            <h3 class="text-xl font-black text-slate-900">០២</h3>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 bg-white p-8 rounded-3xl border border-slate-100 shadow-sm">
          <h4 class="font-bold text-slate-900 mb-6 flex items-center gap-2">
            <CalendarIcon class="w-5 h-5 text-indigo-500" /> ម៉ោងសិក្សាបន្ទាប់
          </h4>
          <div class="relative pl-8 border-l-2 border-indigo-100 space-y-8">
            <div v-for="(cls, index) in nextClasses" :key="index" class="relative">
              <div class="absolute -left-[41px] top-0 w-4 h-4 rounded-full bg-white border-4 border-indigo-500"></div>
              <div class="bg-slate-50 p-5 rounded-2xl flex justify-between items-center">
                <div>
                  <span class="text-xs font-bold text-indigo-500">{{ cls.time }}</span>
                  <h5 class="text-md font-black text-slate-900">{{ cls.subject }}</h5>
                  <p class="text-xs text-slate-500">ដោយ: {{ cls.teacher }}</p>
                </div>
                <div class="text-right">
                  <span class="bg-white px-3 py-1 rounded-lg border border-slate-200 text-xs font-bold text-slate-600">
                    បន្ទប់: {{ cls.room }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm">
          <h4 class="font-bold text-slate-900 mb-6 flex items-center gap-2">
            <ClipboardListIcon class="w-5 h-5 text-orange-500" /> កិច្ចការត្រូវធ្វើ
          </h4>
          <div class="space-y-4">
            <div v-for="(task, index) in homeworks" :key="index" class="flex gap-4 p-4 rounded-2xl hover:bg-slate-50 transition-all border border-transparent hover:border-slate-100 group">
              <input type="checkbox" class="mt-1 rounded text-indigo-600 focus:ring-indigo-500" />
              <div>
                <p class="text-sm font-bold text-slate-800 group-hover:text-indigo-600 transition-all">{{ task.title }}</p>
                <p class="text-[10px] font-bold text-red-400 mt-1 uppercase">ឈប់ទទួល: {{ task.deadline }}</p>
              </div>
            </div>
          </div>
          <button class="w-full mt-6 py-3 border-2 border-dashed border-slate-200 rounded-2xl text-xs font-bold text-slate-400 hover:border-indigo-300 hover:text-indigo-500 transition-all">
            មើលកិច្ចការទាំងអស់
          </button>
        </div>
      </div>
    </div>
  </StudentLayout>
</template>

<script setup>
import StudentLayout from '@/layouts/StudentLayout.vue';
import { CalendarIcon, BookOpenIcon, ClipboardListIcon, AwardIcon } from 'lucide-vue-next';

const nextClasses = [
  { time: '១០:៣០ - ១១:៣០ ព្រឹក', subject: 'អក្សរសាស្ត្រខ្មែរ', teacher: 'អ្នកគ្រូ ចិន្តា', room: 'B-04' },
  { time: '០១:០០ - ០២:៣០ រសៀល', subject: 'ភាសាអង់គ្លេស', teacher: 'Mr. David', room: 'Lab-01' },
];

const homeworks = [
  { title: 'ដោះស្រាយលំហាត់រូបវិទ្យា ទំព័រ ៥៤', deadline: 'ថ្ងៃនេះ' },
  { title: 'សរសេរអត្ថបទតែងសេចក្តី', deadline: 'ថ្ងៃស្អែក' },
  { title: 'គូរគំនូរបែបធម្មជាតិ', deadline: '២ ថ្ងៃទៀត' },
];
</script> -->


<template>
  
</template>