<template>
    <div class="bg-blue-400 text-white rounded-t-xl p-5 shadow-sm">
        <p class="text-sm font-medium mb-3 opacity-90">តម្រងស្វែងរក</p>
        <div class="flex flex-col md:flex-row items-end gap-6">
            <div class="w-full md:w-64">
                <label class="block text-sm font-medium mb-1.5 opacity-90">ឆ្នាំសិក្សា</label>
                <select v-model="filterYear"
                    class="w-full text-gray-800 rounded-lg px-3 py-2 text-sm cursor-pointer focus:ring-2 focus:ring-blue-300">
                    <option value="" disabled>ជ្រើសរើសឆ្នាំ</option>
                    <option v-for="year in academicYears" :key="year.id" :value="year.id">{{ year.year_name }}
                    </option>
                </select>
            </div>
            <div class="w-full md:w-64">
                <label class="block text-sm font-medium mb-1.5 opacity-90">ថ្នាក់</label>
                <select v-model="filterClass"
                    class="w-full text-gray-800 rounded-lg px-3 py-2 text-sm cursor-pointer focus:ring-2 focus:ring-blue-300">
                    <option value="" disabled>ជ្រើសរើសថ្នាក់</option>
                    <option v-for="cls in studentClasses" :key="cls.id" :value="cls.id">{{ cls.grade_level }}{{
                        cls.name }}</option>
                </select>
            </div>
            <button @click="fetchReportData" :disabled="loading"
                class="bg-emerald-500 hover:bg-emerald-600 px-6 py-2 rounded-lg text-sm font-medium transition flex items-center gap-2">
                {{ loading ? 'កំពុងផ្ទុក...' : 'បង្ហាញទិន្នន័យ' }}
            </button>
        </div>
    </div>

    <div v-if="loadError" class="mt-4 p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-600 text-sm font-bold">
        {{ loadError }}
    </div>

    <div class="bg-white rounded-b-xl border border-gray-200 shadow-sm overflow-hidden min-h-[300px]">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-gray-700 text-sm">
                        <th class="py-3 px-4 text-center">ល.រ</th>
                        <th class="py-3 px-4">ឈ្មោះសិស្ស</th>
                        <th class="py-3 px-4">ភេទ</th>
                        <th class="py-3 px-4">ថ្ងៃ ខែ ឆ្នាំកំណើត</th>
                        <th class="py-3 px-4">ថ្នាក់ទី</th>
                        <th class="py-3 px-4">មកពី</th>
                        <th class="py-3 px-4">ផ្សេងៗ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    <tr v-for="(student, index) in students" :key="student.id" class="hover:bg-gray-50">
                        <td class="py-3 px-4 text-center text-gray-500">{{ toKhmerNumber(index + 1) }}</td>
                        <td class="py-3 px-4 font-medium text-gray-900">{{ student.name_kh || student.name }}</td>
                        <td class="py-3 px-4">
                            {{ student.gender === 'male' ? 'ប្រុស' : 'ស្រី' }}
                        </td>
                        <td class="py-3 px-4">{{ formatDate(student.date_of_birth) }}</td>
                        <td class="py-3 px-4 font-medium text-gray-900">{{ student.from_class }}</td>
                        <td class="py-3 px-4 font-medium text-gray-900">{{ student.pri_school }}</td>
                        <td class="py-3 px-4 text-gray-600">{{ student.note }}</td>
                    </tr>
                    <tr v-if="students.length === 0 && !loading">
                        <td colspan="7" class="py-10 text-center text-gray-400">មិនទាន់មានទិន្នន័យសម្រាប់បង្ហាញ</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="p-4 bg-white border-t border-gray-100 flex justify-between items-center">
            <div class="text-sm text-gray-600">ទិន្នន័យសរុប៖ <span class="font-bold text-gray-900">{{
                toKhmerNumber(students.length) }}</span> នាក់</div>
            <div class="space-x-3">
                <button @click="exportPDF"
                    class="bg-white border border-blue-400 text-blue-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-50 transition">
                    ទាញយក PDF
                </button>
                <button @click="exportExcel"
                    class="bg-white border border-green-300 text-green-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-50 transition">
                    នាំចេញ Excel
                </button>
            </div>
        </div>
    </div>

    <div class="mt-10 text-right leading-8 justify-end">
        <div class=" flex flex-col items-end ">
            <div class="text-center">
                <p>ថ្ងៃ............. ខែ............. ឆ្នាំ............. ព.ស...............</p>
                <p>នរា ថ្ងៃទី........ ខែ........ ឆ្នាំ២០........</p>
                <p>គ្រូបន្ទប់ថ្នាក់</p>
            </div>
        </div>
        <div class="flex justify-between">

            <div class="text-center font-moul ">
                <p>បានឃើញ និងឯកភាព</p>
                <p>នាយកសាលា</p>
            </div>
        </div>
    </div>




    <div ref="printArea" class="pdf-export-area">
        <div class="doc-header">
            <p class="kingdom-title">ព្រះរាជាណាចក្រកម្ពុជា</p>
            <p class="kingdom-motto">ជាតិ សាសនា ព្រះមហាក្សត្រ</p>
            <div class="ornament">
                <span class="ornament-line"></span>
                <span class="ornament-icon">❖</span>
                <span class="ornament-icon">❖</span>
                <span class="ornament-icon">❖</span>
                <span class="ornament-line"></span>
            </div>
        </div>

        <div class="doc-office">
            <p>{{ ministryLine1 }}</p>
            <p>{{ ministryLine2 }}</p>
        </div>

        <h2 class="doc-title">បញ្ជីរាយនាមសិស្សថ្នាក់ទី &laquo;
            <span class="latin-text">{{ selectedClassLabel }}</span>
            &raquo; {{ selectedYearLabel }}
        </h2>
        <table class="pdf-table">
            <thead>
                <tr>
                    <th style="width:6%">ល.រ</th>
                    <th style="width:22%">ឈ្មោះសិស្ស</th>
                    <th style="width:8%">ភេទ</th>
                    <th style="width:18%">ថ្ងៃ ខែ ឆ្នាំកំណើត</th>
                    <th style="width:14%">ថ្នាក់ទី</th>
                    <th style="width:18%">មកពី</th>
                    <th style="width:14%">ផ្សេងៗ</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(student, index) in students" :key="'pdf-' + student.id">
                    <td style="text-align:center">{{ toKhmerNumber(index + 1) }}</td>
                    <td>{{ student.name_kh || student.name }}</td>
                    <td style="text-align:center">{{ student.gender === 'male' ? 'ប្រុស' : 'ស្រី' }}</td>
                    <td style="text-align:center">{{ formatDate(student.date_of_birth) }}</td>
                    <td style="text-align:center">{{ student.from_class }}</td>
                    <td>{{ student.pri_school }}</td>
                    <td>{{ student.note }}</td>
                </tr>
            </tbody>
        </table>

        <div class="doc-summary">
            <strong>
                សរុបចំនួនសិស្ស៖ {{ toKhmerNumber(students.length) }} នាក់
                &nbsp;&nbsp;&nbsp;&nbsp;
                ស្រីចំនួន៖ {{toKhmerNumber(students.filter(s => s.gender === 'female').length)}} នាក់
            </strong>
        </div>

        <div class="doc-date">
            <p>ថ្ងៃ............. ខែ............. ឆ្នាំ............. ព.ស...............</p>
            <p>នរា ថ្ងៃទី........ ខែ........ ឆ្នាំ២០........</p>
            <p class="moul" style="margin-right: 35px;">គ្រូបន្ទប់ថ្នាក់</p>
        </div>
        <div class="doc-footer">

            <div class="signature-left">
                <p class="moul">បានឃើញ និងឯកភាព</p>
                <p class="moul">នាយកសាលា</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/services/authService'
import * as XLSX from 'xlsx-js-style'
import html2pdf from 'html2pdf.js'

const academicYears = ref([])
const studentClasses = ref([])
const students = ref([])
const loading = ref(false)
const loadError = ref('')
const filterYear = ref('')
const filterClass = ref('')
const printArea = ref(null)

// អាចកែសម្រួលឈ្មោះការិយាល័យ/សាលារបស់អ្នកនៅទីនេះ
const ministryLine1 = ref('មន្ទីរអប់រំ យុវជន និងកីឡាខេត្តបាត់ដំបង')
const ministryLine2 = ref('អនុវិទ្យាល័យ នរា')

const selectedClassLabel = computed(() => {
    const cls = studentClasses.value.find(c => c.id === filterClass.value)
    return cls ? `${cls.grade_level}${cls.name}` : ''
})

const selectedYearLabel = computed(() => {
    const year = academicYears.value.find(y => y.id === filterYear.value)
    return year ? toKhmerDigits(year.year_name) : ''
})

// ចំណងជើងឯកសារពេញ៖ ប្រើសម្រាប់ទាំងចំណងជើងក្នុងឯកសារ និងឈ្មោះឯកសារពេលទាញយក
const reportTitle = computed(() => {
    const parts = ['បញ្ជីរាយនាមសិស្សថ្នាក់ទី']
    if (selectedClassLabel.value) parts.push(selectedClassLabel.value)
    if (selectedYearLabel.value) parts.push(`ឆ្នាំ${selectedYearLabel.value}`)
    return parts.join(' ')
})

// ធ្វើអោយឈ្មោះឯកសារមានសុវត្ថិភាព (ដកតួអក្សរដែលប្រព័ន្ធឯកសារមិនអនុញ្ញាត)
const sanitizeFileName = (name) => {
    return name.replace(/[\\/:*?"<>|]/g, '-').trim()
}

const toKhmerDigits = (str) => {
    const map = {
        '0': '០',
        '1': '១',
        '2': '២',
        '3': '៣',
        '4': '៤',
        '5': '៥',
        '6': '៦',
        '7': '៧',
        '8': '៨',
        '9': '៩',
    }

    return String(str).replace(/[0-9]/g, d => map[d])
}

const loadFilters = async () => {
    try {
        const [years, classes] = await Promise.all([api.get('/years'), api.get('/classes')])
        academicYears.value = years.data.data || years.data
        studentClasses.value = classes.data.data || classes.data

        // កំណត់ឆ្នាំសិក្សាដែលសកម្មដោយស្វ័យប្រវត្តិ
        const active = academicYears.value.find(y => y.is_active)
        if (active) filterYear.value = active.id
    } catch (err) {
        console.error(err)
        loadError.value = 'មិនអាចទាញយកឆ្នាំសិក្សា ឬ ថ្នាក់បានទេ'
    }
}

const fetchReportData = async () => {
    if (!filterClass.value) return alert('សូមជ្រើសរើសថ្នាក់ជាមុនសិន')
    loading.value = true
    loadError.value = ''
    try {
        const res = await api.get(`/classes/${filterClass.value}/students`)
        students.value = res.data.data || res.data
    } catch (err) {
        console.error(err)
        loadError.value = 'មានបញ្ហាក្នុងការទាញយកទិន្នន័យ'
    } finally {
        loading.value = false
    }
}

const toKhmerNumber = (num) => {
    const map = { '0': '០', '1': '១', '2': '២', '3': '៣', '4': '៤', '5': '៥', '6': '៦', '7': '៧', '8': '៨', '9': '៩' };
    return String(num).replace(/[0-9]/g, (s) => map[s]);
}

const formatDate = (dateString) => {
    if (!dateString) return '';

    const d = new Date(dateString);
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();
    return toKhmerNumber(`${day}/${month}/${year}`);
}

onMounted(loadFilters)

const THIN_BORDER = { style: 'thin', color: { rgb: '000000' } }
const ALL_BORDERS = { top: THIN_BORDER, bottom: THIN_BORDER, left: THIN_BORDER, right: THIN_BORDER }

// ម៉ូតអក្សរដូចគ្នានឹងឯកសារ PDF (Khmer OS Muol Light សម្រាប់ក្បាល និង Khmer OS Battambang សម្រាប់តួឯកសារ)
const kingdomStyle = {
    font: { name: 'Khmer OS Muol Light', bold: true, sz: 16 },
    alignment: { horizontal: 'center', vertical: 'center' },
}
const mottoStyle = {
    font: { name: 'Khmer OS Muol Light', bold: true, sz: 13 },
    alignment: { horizontal: 'center', vertical: 'center' },
}
const officeStyle = {
    font: { name: 'Khmer OS Muol Light', bold: true, sz: 13 },
    alignment: { horizontal: 'left', vertical: 'center' },
}
const titleStyle = {
    font: { name: 'Khmer OS Muol Light', bold: true, sz: 13 },
    alignment: { horizontal: 'center', vertical: 'center' },
}
const tableHeaderStyle = {
    font: { name: 'Khmer OS Battambang', bold: true, sz: 12 },
    alignment: { horizontal: 'center', vertical: 'center' },
    border: ALL_BORDERS,
}
const tableCellStyle = {
    font: { name: 'Khmer OS Battambang', sz: 12 },
    alignment: { horizontal: 'center', vertical: 'center' },
    border: ALL_BORDERS,
}
const totalStyle = {
    font: { name: 'Khmer OS Battambang', bold: true, sz: 12 },
    alignment: { horizontal: 'left', vertical: 'center' },
}
const footerCenterStyle = {
    font: { name: 'Khmer OS Battambang', bold: true, sz: 12 },
    alignment: { horizontal: 'center', vertical: 'center' },
}

const styleCell = (ws, r, c, style) => {
    const addr = XLSX.utils.encode_cell({ r, c })
    if (!ws[addr]) ws[addr] = { t: 's', v: '' }
    ws[addr].s = style
}

// នាំចេញ Excel ជាទម្រង់ក្បាលក្រដាស់រាជរដ្ឋាភិបាល ដូចគ្នានឹងទម្រង់ PDF (រួមទាំងផ្នែកសរុប និងហត្ថលេខា)
const exportExcel = () => {
    if (students.value.length === 0) return

    const tableHeader = ['ល.រ', 'ឈ្មោះសិស្ស', 'ភេទ', 'ថ្ងៃខែឆ្នាំកំណើត', 'ថ្នាក់ទី', 'មកពី', 'ផ្សេងៗ']
    const colCount = tableHeader.length

    const headerRows = [
        ['ព្រះរាជាណាចក្រកម្ពុជា'],
        ['ជាតិ សាសនា ព្រះមហាក្សត្រ'],
        [],
        [ministryLine1.value],
        [ministryLine2.value],
        [],
        [`បញ្ជីរាយនាមសិស្សថ្នាក់ទី «${selectedClassLabel.value}» ${selectedYearLabel.value}`],
        [],
    ]

    const tableRows = students.value.map((s, i) => [
        toKhmerNumber(i + 1),
        s.name_kh || s.name,
        s.gender === 'male' ? 'ប្រុស' : 'ស្រី',
        formatDate(s.date_of_birth),
        s.from_class,
        s.pri_school,
        s.note || '',
    ])

    // ត្រូវគណនាទីតាំងជួរជាមុន មុននឹងបូកបញ្ចូលទាំងអស់ទៅជា aoa តែមួយ
    const tableHeaderRowIdx = headerRows.length
    const tableStartRowIdx = tableHeaderRowIdx + 1
    const tableEndRowIdx = tableStartRowIdx + tableRows.length - 1

    const totalRowIdx = tableEndRowIdx + 2          // ជួរទទេមួយ រួចជួរសរុប
    const dateLineRowIdx = totalRowIdx + 2           // ជួរទទេមួយ រួចជួរកាលបរិច្ឆេទ
    const signatureRowIdx = dateLineRowIdx + 1       // ជួរនាយកសាលានៅជាប់ខាងក្រោមជួរកាលបរិច្ឆេទ

    const footerRows = [
        [],                                                          // tableEndRowIdx + 1
        [`សរុបចំនួន៖ ${toKhmerNumber(students.value.length)} នាក់`],  // totalRowIdx
        [],                                                          // totalRowIdx + 1
        ['ធ្វើនៅ.....................ថ្ងៃទី...........ខែ...........ឆ្នាំ...........'], // dateLineRowIdx
        ['នាយកសាលា'],                                                 // signatureRowIdx
    ]

    const aoa = [...headerRows, tableHeader, ...tableRows, ...footerRows]
    const ws = XLSX.utils.aoa_to_sheet(aoa)

    ws['!merges'] = [
        { s: { r: 0, c: 0 }, e: { r: 0, c: colCount - 1 } },              // ព្រះរាជាណាចក្រកម្ពុជា
        { s: { r: 1, c: 0 }, e: { r: 1, c: colCount - 1 } },              // ជាតិ សាសនា ព្រះមហាក្សត្រ
        { s: { r: 3, c: 0 }, e: { r: 3, c: colCount - 1 } },              // ministryLine1
        { s: { r: 4, c: 0 }, e: { r: 4, c: colCount - 1 } },              // ministryLine2
        { s: { r: 6, c: 0 }, e: { r: 6, c: colCount - 1 } },              // ចំណងជើងបញ្ជី
        { s: { r: dateLineRowIdx, c: 0 }, e: { r: dateLineRowIdx, c: colCount - 1 } },   // ជួរកាលបរិច្ឆេទ
        { s: { r: signatureRowIdx, c: 0 }, e: { r: signatureRowIdx, c: colCount - 1 } }, // ជួរនាយកសាលា
    ]

    // អនុវត្តម៉ូតលើក្បាលឯកសារ
    styleCell(ws, 0, 0, kingdomStyle)
    styleCell(ws, 1, 0, mottoStyle)
    styleCell(ws, 3, 0, officeStyle)
    styleCell(ws, 4, 0, officeStyle)
    styleCell(ws, 6, 0, titleStyle)

    // អនុវត្តម៉ូតលើក្បាលតារាង
    for (let c = 0; c < colCount; c++) {
        styleCell(ws, tableHeaderRowIdx, c, tableHeaderStyle)
    }

    // អនុវត្តម៉ូតលើទិន្នន័យតារាង
    for (let r = tableStartRowIdx; r <= tableEndRowIdx; r++) {
        for (let c = 0; c < colCount; c++) {
            styleCell(ws, r, c, tableCellStyle)
        }
    }

    // អនុវត្តម៉ូតលើផ្នែកសរុប និងហត្ថលេខា
    styleCell(ws, totalRowIdx, 0, totalStyle)
    styleCell(ws, dateLineRowIdx, 0, footerCenterStyle)
    styleCell(ws, signatureRowIdx, 0, footerCenterStyle)

    ws['!cols'] = [
        { wch: 6 }, { wch: 24 }, { wch: 8 }, { wch: 18 }, { wch: 12 }, { wch: 16 }, { wch: 16 }
    ]

    ws['!rows'] = [{ hpt: 24 }]

    const wb = XLSX.utils.book_new()
    XLSX.utils.book_append_sheet(wb, ws, 'បញ្ជីសិស្ស')
    XLSX.writeFile(wb, `${sanitizeFileName(reportTitle.value)}.xlsx`)
}

// នាំចេញ PDF ជាទម្រង់ក្បាលក្រដាស់រាជរដ្ឋាភិបាល ដោយប្រើ html2pdf.js
const exportPDF = async () => {
    if (students.value.length === 0) return
    if (!printArea.value) return

    const opt = {
        margin: [12, 10, 12, 10],
        filename: `${sanitizeFileName(reportTitle.value)}.pdf`,
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true, backgroundColor: '#ffffff' },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
    }

    // ត្រូវប្រាកដថា Font Battambang / Moul ត្រូវបានផ្ទុករួចមុននឹងថតរូប
    if (document.fonts && document.fonts.ready) {
        await document.fonts.ready
    }

    printArea.value.style.display = 'block'
    try {
        await html2pdf().set(opt).from(printArea.value).save()
    } catch (err) {
        console.error(err)
        loadError.value = 'មានបញ្ហាក្នុងការនាំចេញ PDF'
    } finally {
        printArea.value.style.display = 'none'
    }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Battambang:wght@400;700&family=Moul&display=swap');

.pdf-export-area {
    display: none;
    width: 190mm;
    padding: 14px 24px 24px;
    background: #ffffff;
    color: #000000;
    font-family: 'Battambang', sans-serif;
}

.doc-header {
    text-align: center;
    margin-bottom: 10px;
}

.kingdom-title {
    font-family: 'Moul', serif;
    font-size: 14px;
    font-weight: 500;
    margin: 0;
    letter-spacing: 0.5px;
}

.kingdom-motto {
    font-family: 'Moul', serif;
    font-weight: 500;
    font-size: 14px;
    margin: 3px 0 10px;
}

.ornament {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.ornament-line {
    width: 70px;
    height: 1px;
    background: #000000;
}

.ornament-icon {
    font-size: 11px;
    line-height: 1;
}

.doc-office {
    font-family: 'Moul', serif;
    font-weight: 500;
    font-size: 14px;
    margin: 14px 0 18px;
}

.doc-office p {
    margin: 2px 0;
}

.doc-title {
    font-family: 'Moul', Arial, sans-serif;
    font-weight: 500;
    font-size: 14px;
    text-align: center;
    margin: 0 0 16px;
}

.latin-text {
    font-family: 'Arial', sans-serif;
    font-weight: bold;
}

.pdf-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
    text-align: center;

}

.pdf-table th,
.pdf-table td {
    border: 1px solid #000000;
    padding: 6px 8px;
}

.pdf-table th {
    font-weight: 700;
    text-align: center;
    background: #ffffff;
}

.doc-total {
    margin-top: 10px;
    font-size: 13px;
    font-weight: 700;
    text-align: left;
}

.doc-summary {
    margin-top: 15px;
    font-size: 13px;
    font-weight: bold;
}

.doc-date {
    text-align: right;
    font-size: 13px;
    margin-top: 18px;
    line-height: 1.8;
}

.doc-footer {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-top: 35px;
}

.signature-left,
.signature-right {
    width: 40%;
    text-align: center;
}



.moul {
    font-family: 'Moul', serif;
    font-size: 14px;
    margin-right: 28px;
}

.signature-right {
    margin-top: 10px;
}
</style>