# Panduan Pengembangan Frontend

Dokumentasi ini menjelaskan konvensi, struktur, dan best practices untuk pengembangan frontend di proyek Laravel + Inertia + Vue ini.

## Tech Stack Frontend

- **Vue 3** - Framework JavaScript dengan Composition API
- **Inertia.js v2** - Modern monolith, SPA tanpa kompleksitas API
- **TypeScript** - Type safety untuk JavaScript
- **PrimeVue v4** - UI Component Library
- **Tailwind CSS v3** - Utility-first CSS framework
- **Vite** - Build tool & dev server
- **Ziggy v2** - Named routing dari Laravel ke JavaScript

## Struktur Folder Frontend

```
resources/js/
├── Components/          # Reusable Vue components
├── Layouts/            # Layout components (e.g., AppLayout, GuestLayout)
├── Pages/              # Inertia pages (route endpoints)
├── Types/              # TypeScript type definitions
├── Utils/              # Helper functions & utilities
├── app.ts              # Main application entry
└── bootstrap.ts        # Bootstrap config (axios, echo, dll)
```

## Konvensi Penamaan

### File & Komponen

- **Pages**: PascalCase dengan suffix `View` → `LoginView.vue`, `DashboardView.vue`
- **Components**: PascalCase → `UserCard.vue`, `DataTable.vue`
- **Layouts**: PascalCase dengan suffix `Layout` → `AppLayout.vue`, `AuthLayout.vue`
- **Utils/Composables**: camelCase → `useAuth.ts`, `formatDate.ts`

### Variabel & Props

- **Variables**: camelCase → `userName`, `isActive`
- **Constants**: UPPER_SNAKE_CASE → `MAX_RETRIES`, `API_BASE_URL`
- **Props**: camelCase dengan type definition
- **Events**: kebab-case → `@update-user`, `@delete-item`

## TypeScript Conventions

```typescript
// Definisikan props dengan type safety
interface Props {
  user: User
  items?: Array<Item>
  isLoading?: boolean
}

const props = defineProps<Props>()

// Gunakan type inference untuk reactive values
const count = ref(0) // type: Ref<number>
const user = ref<User | null>(null) // explicit type

// Emit events dengan type
const emit = defineEmits<{
  'update:modelValue': [value: string]
  'submit': [data: FormData]
}>()
```

## Inertia.js Patterns

### Pages (Route Endpoints)

Pages adalah komponen Vue yang di-render dari controller Laravel via `Inertia::render()`.

```vue
<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'

interface Props {
  users: Array<User>
  filters?: { search?: string }
}

const props = defineProps<Props>()
</script>

<template>
  <AppLayout>
    <Head title="Users" />
    
    <div class="py-12">
      <!-- Page content -->
    </div>
  </AppLayout>
</template>
```

### Navigation

Gunakan `<Link>` component untuk navigasi SPA:

```vue
<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
</script>

<template>
  <!-- Named route dengan Ziggy -->
  <Link :href="route('users.index')" class="text-blue-600">
    Users
  </Link>
  
  <!-- Dengan method -->
  <Link :href="route('logout')" method="post" as="button">
    Logout
  </Link>
</template>
```

### Forms

Gunakan `useForm()` untuk form handling dengan validasi:

```vue
<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'

const form = useForm({
  name: '',
  email: '',
  password: '',
})

const submit = () => {
  form.post(route('users.store'), {
    preserveScroll: true,
    onSuccess: () => form.reset(),
  })
}
</script>

<template>
  <form @submit.prevent="submit">
    <input v-model="form.name" type="text" />
    <div v-if="form.errors.name" class="text-red-600">
      {{ form.errors.name }}
    </div>
    
    <button type="submit" :disabled="form.processing">
      Submit
    </button>
  </form>
</template>
```

### Inertia v2 Features

#### Deferred Props

Untuk data yang lambat dimuat (e.g., heavy queries):

```typescript
// Controller
return Inertia::render('Dashboard', [
  'stats' => fn() => $this->getStats(), // Deferred
  'user' => $user, // Immediate
])
```

```vue
<!-- Component -->
<template>
  <!-- Tampilkan skeleton saat loading -->
  <div v-if="!stats" class="animate-pulse">
    <div class="h-4 bg-gray-200 rounded"></div>
  </div>
  <div v-else>{{ stats }}</div>
</template>
```

#### Prefetching

Tingkatkan UX dengan prefetch links saat hover:

```vue
<Link :href="route('users.show', user.id)" prefetch>
  {{ user.name }}
</Link>
```

#### Polling

Auto-refresh data secara berkala:

```vue
<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import { onMounted, onUnmounted } from 'vue'

let interval: number

onMounted(() => {
  interval = setInterval(() => {
    router.reload({ only: ['stats'] })
  }, 5000) // Poll every 5 seconds
})

onUnmounted(() => clearInterval(interval))
</script>
```

## Composition API Patterns

### Script Setup

Selalu gunakan `<script setup>` untuk komponen baru:

```vue
<script setup lang="ts">
// Imports
import { ref, computed, onMounted } from 'vue'

// Props
interface Props {
  title: string
}
const props = defineProps<Props>()

// Emits
const emit = defineEmits<{
  'update': [value: string]
}>()

// State
const count = ref(0)

// Computed
const doubled = computed(() => count.value * 2)

// Methods
const increment = () => {
  count.value++
  emit('update', count.value.toString())
}

// Lifecycle
onMounted(() => {
  console.log('Component mounted')
})
</script>
```

### Composables

Buat reusable logic dengan composables:

```typescript
// usePermission.ts
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

export function usePermission() {
  const page = usePage()
  
  const user = computed(() => page.props.auth?.user)
  
  const can = (permission: string) => {
    return user.value?.permissions?.includes(permission) ?? false
  }
  
  const hasRole = (role: string) => {
    return user.value?.roles?.includes(role) ?? false
  }
  
  return { can, hasRole, user }
}
```

Gunakan di component:

```vue
<script setup lang="ts">
import { usePermission } from '@/Utils/usePermission'

const { can, hasRole } = usePermission()
</script>

<template>
  <button v-if="can('delete-users')">Delete</button>
  <div v-if="hasRole('admin')">Admin Panel</div>
</template>
```

## Tailwind CSS Guidelines

### Class Organization

Urutkan classes dengan konsisten:

1. Layout (display, position)
2. Box model (width, height, margin, padding)
3. Typography
4. Visual (background, border, shadow)
5. Misc (cursor, transition, animation)

```vue
<div class="flex items-center justify-between w-full p-4 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg shadow-sm hover:bg-gray-50 transition-colors">
```

### Component Classes

Untuk classes yang sering digunakan, buat utility atau component:

```typescript
// utils/classes.ts
export const buttonVariants = {
  primary: 'px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500',
  secondary: 'px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300',
  danger: 'px-4 py-2 text-white bg-red-600 rounded-lg hover:bg-red-700',
}
```

```vue
<button :class="buttonVariants.primary">Save</button>
```

### Responsive Design

Gunakan mobile-first approach:

```vue
<!-- Mobile first, tablet (md), desktop (lg) -->
<div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
```

### Dark Mode (jika digunakan)

```vue
<div class="bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
```

## PrimeVue Components

Project ini menggunakan **PrimeVue v4** sebagai UI component library.

### Import Components

PrimeVue components tidak perlu di-import manual karena sudah auto-import:

```vue
<script setup lang="ts">
// ✅ Langsung pakai, tidak perlu import
</script>

<template>
  <Button label="Save" />
  <Dialog v-model:visible="visible" header="Title">
    Content
  </Dialog>
</template>
```

### Common PrimeVue Components

**Buttons**
```vue
<Button label="Save" severity="primary" />
<Button label="Cancel" severity="secondary" outlined />
<Button label="Delete" severity="danger" icon="pi pi-trash" />
<Button icon="pi pi-check" rounded />
```

**Form Controls**
```vue
<!-- Input Text -->
<InputText v-model="value" placeholder="Enter text" />

<!-- Password -->
<Password v-model="password" :feedback="false" toggleMask />

<!-- Select -->
<Select v-model="selected" :options="options" option-label="name" option-value="id" />

<!-- MultiSelect -->
<MultiSelect v-model="selected" :options="options" option-label="name" display="chip" />

<!-- Calendar/DatePicker -->
<DatePicker v-model="date" dateFormat="dd/mm/yy" />
```

**Feedback**
```vue
<!-- Toast -->
import { useToast } from 'primevue'
const toast = useToast()

toast.add({
  severity: 'success', // success, info, warn, error
  summary: 'Success',
  detail: 'Data saved successfully',
  life: 3000
})

<!-- Confirm Dialog -->
import { useConfirm } from 'primevue'
const confirm = useConfirm()

confirm.require({
  message: 'Are you sure?',
  header: 'Confirmation',
  icon: 'pi pi-exclamation-triangle',
  accept: () => {
    // Delete action
  }
})
```

### PrimeVue + Tailwind Integration

PrimeVue components bisa dikombinasikan dengan Tailwind classes:

```vue
<Button class="mt-4 w-full" label="Submit" />
<div class="flex gap-2">
  <Button label="Cancel" severity="secondary" />
  <Button label="Save" severity="primary" />
</div>
```

## Ziggy (Named Routes)

Akses named routes Laravel di JavaScript:

```typescript
import { route } from 'ziggy-js'

// Simple route
route('users.index') // /users

// With parameters
route('users.show', { user: 1 }) // /users/1

// With query string
route('users.index', { _query: { search: 'john' } }) // /users?search=john

// Check current route
route().current('users.*') // true if on any users route
```

## Auto-imports

File `components.d.ts` mengaktifkan auto-import untuk:

- Vue components di folder `Components/`
- Inertia components (`Link`, `Head`)
- Vue APIs (`ref`, `computed`, `watch`, dll.)

Tidak perlu import manual:

```vue
<script setup lang="ts">
// ❌ Tidak perlu
// import { ref } from 'vue'
// import { Link } from '@inertiajs/vue3'

// ✅ Langsung pakai
const count = ref(0)
</script>

<template>
  <Link :href="route('home')">Home</Link>
</template>
```

## Best Practices

### 1. Component Composition

✅ **DO**: Buat small, reusable components

```vue
<!-- UserCard.vue -->
<template>
  <div class="p-4 bg-white rounded-lg shadow">
    <h3>{{ user.name }}</h3>
    <p>{{ user.email }}</p>
  </div>
</template>
```

❌ **DON'T**: Buat monolithic components dengan terlalu banyak logic

### 2. Props Validation

✅ **DO**: Selalu definisikan types untuk props

```typescript
interface Props {
  user: User
  editable?: boolean
}
const props = withDefaults(defineProps<Props>(), {
  editable: false
})
```

### 3. Event Handling

✅ **DO**: Gunakan descriptive event names

```vue
<UserCard @user-updated="handleUpdate" @user-deleted="handleDelete" />
```

❌ **DON'T**: Gunakan generic names seperti `@click` untuk business logic

### 4. Conditional Rendering

✅ **DO**: Gunakan `v-if` untuk expensive renders, `v-show` untuk toggle visibility

```vue
<Modal v-if="isOpen">Heavy content</Modal>
<Sidebar v-show="isVisible">Frequently toggled</Sidebar>
```

### 5. List Rendering

✅ **DO**: Selalu gunakan `:key` dengan unique identifier

```vue
<div v-for="user in users" :key="user.id">
  {{ user.name }}
</div>
```

### 6. Type Safety

✅ **DO**: Leverage TypeScript untuk type safety

```typescript
// types/index.ts
export interface User {
  id: number
  name: string
  email: string
  roles?: string[]
}

export interface PaginatedResponse<T> {
  data: T[]
  meta: {
    current_page: number
    last_page: number
    total: number
  }
}
```

## Performance Tips

### 1. Lazy Load Heavy Components

```typescript
import { defineAsyncComponent } from 'vue'

const HeavyChart = defineAsyncComponent(() => 
  import('@/Components/HeavyChart.vue')
)
```

### 2. Optimize Inertia Requests

```typescript
// Hanya reload data yang diperlukan
router.reload({ only: ['users'] })

// Preserve scroll position
router.visit(url, { preserveScroll: true })

// Preserve state
router.visit(url, { preserveState: true })
```

### 3. Debounce Search Input

```vue
<script setup lang="ts">
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { debounce } from 'lodash-es'

const search = ref('')

const performSearch = debounce((value: string) => {
  router.get(route('users.index'), 
    { search: value }, 
    { preserveState: true }
  )
}, 300)

watch(search, performSearch)
</script>
```

## Development Workflow

### Running Dev Server

```bash
# Start Vite dev server
npm run dev

# Atau dengan Laravel
composer run dev
```

### Building for Production

```bash
# Build assets
npm run build

# Lint & format
npm run lint
vendor/bin/pint
```

### Debugging

#### Vue DevTools

Install Vue DevTools browser extension untuk inspect components, state, dan events.

#### Vite Error

Jika muncul error "Unable to locate file in Vite manifest", run:

```bash
npm run build
```

Atau minta user untuk run `npm run dev` atau `composer run dev`.

## Common Patterns

### Dialog/Modal Pattern (PrimeVue)

Pattern untuk form modal dengan CRUD operations:

```vue
<!-- UserFormModal.vue -->
<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useToast, useConfirm } from 'primevue'
import { yupResolver } from '@primevue/forms/resolvers/yup'
import * as yup from 'yup'
import axios from 'axios'

const toast = useToast()
const confirm = useConfirm()

const emit = defineEmits(['data-created', 'data-updated', 'data-deleted'])

const dialogVisible = ref(false)
const editMode = ref(false)
const loading = ref(false)

const formData = reactive({
  id: null,
  name: '',
  email: '',
})

const formErrors = ref()
const resolver = yupResolver(
  yup.object().shape({
    name: yup.string().required('Name is required'),
    email: yup.string().required('Email is required').email(),
  })
)

function closeDialog() {
  dialogVisible.value = false
}

function addAction() {
  dialogVisible.value = true
  editMode.value = false
  formErrors.value = []
  
  formData.id = null
  formData.name = ''
  formData.email = ''
}

function addSubmitAction(event) {
  if (event.valid) {
    loading.value = true
    axios.post(route('users.store'), formData)
      .then((response) => {
        toast.add({
          severity: 'success',
          summary: 'Success',
          detail: response.data.message,
          life: 3000,
        })
        closeDialog()
        emit('data-created')
      })
      .catch((error) => {
        formErrors.value = error.response.data.errors
      })
      .finally(() => {
        loading.value = false
      })
  }
}

function editAction(data) {
  dialogVisible.value = true
  editMode.value = true
  formErrors.value = []
  
  formData.id = data.id
  formData.name = data.name
  formData.email = data.email
}

function deleteAction(data) {
  confirm.require({
    message: 'Do you want to delete this record?',
    header: 'Warning',
    icon: 'pi pi-exclamation-triangle',
    rejectLabel: 'Cancel',
    acceptLabel: 'Delete',
    acceptProps: { severity: 'danger' },
    accept: () => {
      axios.delete(route('users.destroy', data.id))
        .then((response) => {
          toast.add({
            severity: 'success',
            summary: 'Success',
            detail: response.data.message,
            life: 3000,
          })
          emit('data-deleted')
        })
    }
  })
}

defineExpose({
  addAction,
  editAction,
  deleteAction,
})
</script>

<template>
  <Dialog 
    :header="(!editMode ? 'Add' : 'Edit') + ' User'" 
    v-model:visible="dialogVisible" 
    class="w-full max-w-xl" 
    :draggable="false"
    modal
  >
    <AppForm 
      class="flex flex-col gap-2" 
      v-model="formData" 
      v-model:errors="formErrors" 
      :resolver
      @submit="!editMode ? addSubmitAction : editSubmitAction"
    >
      <AppFormField name="name" label="Name" required>
        <AppFormInput id="name" v-model="formData.name" type="text" />
      </AppFormField>
      
      <AppFormField name="email" label="Email" required>
        <AppFormInput id="email" v-model="formData.email" type="email" />
      </AppFormField>
      
      <div class="flex justify-end gap-2 mt-2">
        <Button label="Cancel" severity="secondary" @click.prevent="closeDialog" />
        <Button :label="!editMode ? 'Create' : 'Update'" type="submit" :loading="loading" />
      </div>
    </AppForm>
  </Dialog>
</template>
```

**Usage di parent component:**

```vue
<script setup lang="ts">
import UserFormModal from './Components/UserFormModal.vue'

const userFormModalRef = ref()

const addUser = () => userFormModalRef.value?.addAction()
const editUser = (user) => userFormModalRef.value?.editAction(user)
const deleteUser = (user) => userFormModalRef.value?.deleteAction(user)
</script>

<template>
  <Button label="Add User" @click="addUser" />
  
  <UserFormModal 
    ref="userFormModalRef" 
    @data-created="refreshData"
    @data-updated="refreshData"
    @data-deleted="refreshData"
  />
</template>
```

### DataTable Pattern (PrimeVue)

Pattern untuk server-side datatable dengan filtering & pagination:

```vue
<script setup lang="ts">
import { ref } from 'vue'
import { FilterMatchMode } from '@primevue/core/api'
import { createDataTableHandler } from '@/Core/Handlers/data-table-handler'

// DataTable handler untuk server-side processing
const dtHandler = createDataTableHandler(route('users.datatable'))

// Filter configuration
const filters = ref({
  'name': { value: null, matchMode: FilterMatchMode.CONTAINS },
  'email': { value: null, matchMode: FilterMatchMode.CONTAINS },
  'is_active': { value: null, matchMode: FilterMatchMode.EQUALS },
})

// Selection
const selectedData = ref()

// Refresh data setelah CRUD
const refreshData = () => {
  dtHandler.loadData()
  selectedData.value = null
}
</script>

<template>
  <AppDataTableServer 
    :handler="dtHandler" 
    v-model:selection="selectedData" 
    :filters="filters" 
    data-key="id" 
    filter-display="row"
    empty-message="No data found"
  >
    <!-- Bulk Actions Header -->
    <template #header-start>
      <div v-if="selectedData?.length > 0">
        <div class="flex items-center gap-2 border border-gray-300 rounded-lg px-2">
          <div class="font-bold">
            <Button icon="pi pi-times" variant="text" @click="selectedData = []" rounded />
            <span>{{ selectedData.length }} selected</span>
          </div>
          <Divider layout="vertical" />
          <div>
            <Button icon="pi pi-trash" variant="text" severity="danger" rounded />
          </div>
        </div>
      </div>
    </template>
    
    <!-- Checkbox Column -->
    <Column selectionMode="multiple" headerStyle="width: 3rem" />
    
    <!-- Name Column with Filter -->
    <Column field="name" header="Name" sortable>
      <template #filter="{ filterModel, filterCallback }">
        <InputText 
          v-model="filterModel.value" 
          @change="filterCallback()" 
          placeholder="Search name..."
          size="small"
        />
      </template>
    </Column>
    
    <!-- Email Column -->
    <Column field="email" header="Email" sortable>
      <template #filter="{ filterModel, filterCallback }">
        <InputText 
          v-model="filterModel.value" 
          @change="filterCallback()" 
          size="small"
        />
      </template>
    </Column>
    
    <!-- Status Column with Select Filter -->
    <Column field="is_active" header="Status">
      <template #body="slotProps">
        <Tag 
          :severity="slotProps.data.is_active ? 'success' : 'danger'"
          :value="slotProps.data.is_active ? 'Active' : 'Inactive'"
        />
      </template>
      <template #filter="{ filterModel, filterCallback }">
        <Select 
          v-model="filterModel.value" 
          :options="statusOptions"
          option-value="value"
          option-label="label"
          @change="filterCallback()"
          size="small"
        />
      </template>
    </Column>
    
    <!-- Actions Column -->
    <Column field="id" class="w-16">
      <template #body="slotProps">
        <Button 
          icon="pi pi-pencil" 
          severity="secondary" 
          variant="text" 
          rounded
          @click="editUser(slotProps.data)"
        />
        <Button 
          icon="pi pi-trash" 
          severity="danger" 
          variant="text" 
          rounded
          @click="deleteUser(slotProps.data)"
        />
      </template>
    </Column>
  </AppDataTableServer>
</template>
```

### Popover Menu Pattern

Pattern untuk context menu actions:

```vue
<script setup lang="ts">
const op = ref()
const selectedRowData = ref()
</script>

<template>
  <!-- Trigger button -->
  <Button 
    icon="pi pi-ellipsis-v" 
    variant="text" 
    rounded
    @click="(e) => { op.toggle(e); selectedRowData = data; }"
  />
  
  <!-- Popover menu -->
  <Popover ref="op">
    <div class="flex flex-col gap-1 w-48" @click="op.hide()">
      <span class="font-bold mb-1">Options</span>
      
      <Button 
        icon="pi pi-pencil" 
        label="Edit" 
        severity="secondary" 
        variant="text" 
        class="w-full justify-start"
        size="small"
        @click="editAction"
      />
      
      <Button 
        icon="pi pi-trash" 
        label="Delete" 
        severity="danger" 
        variant="text" 
        class="w-full justify-start"
        size="small"
        @click="deleteAction"
      />
    </div>
  </Popover>
</template>
```

### Form Validation Pattern

Gunakan Yup + PrimeVue Forms untuk form validation:

```vue
<script setup lang="ts">
import { yupResolver } from '@primevue/forms/resolvers/yup'
import * as yup from 'yup'

const formData = reactive({
  name: '',
  email: '',
  age: null,
})

const formErrors = ref()

const resolver = yupResolver(
  yup.object().shape({
    name: yup.string().required('Name is required').min(3, 'Min 3 characters'),
    email: yup.string().required('Email is required').email('Invalid email'),
    age: yup.number().required('Age is required').min(18, 'Must be 18+'),
  })
)

function onSubmit(event) {
  if (event.valid) {
    // Submit logic
  }
}
</script>

<template>
  <AppForm 
    v-model="formData" 
    v-model:errors="formErrors" 
    :resolver
    @submit="onSubmit"
  >
    <AppFormField name="name" label="Name" required>
      <AppFormInput v-model="formData.name" />
    </AppFormField>
    
    <AppFormField name="email" label="Email" required>
      <AppFormInput v-model="formData.email" type="email" />
    </AppFormField>
    
    <Button type="submit" label="Submit" />
  </AppForm>
</template>
```

### Permission Check Pattern

Pattern untuk conditional rendering berdasarkan permission:

```vue
<script setup lang="ts">
import { can } from '@/Core/Utils/permission-check'
</script>

<template>
  <!-- Single permission -->
  <Button v-if="can('user.create')" label="Add User" />
  
  <!-- Multiple permissions (OR) -->
  <div v-if="can(['user.update', 'user.delete'])">
    <Button label="Edit" />
    <Button label="Delete" />
  </div>
  
  <!-- Check in v-for -->
  <Column v-if="can('user.manage')">
    <template #body="slotProps">
      <!-- Actions -->
    </template>
  </Column>
</template>
```

## Checklist Sebelum Commit

- [ ] TypeScript tidak ada error (`npm run type-check` jika ada)
- [ ] Component menggunakan proper type definitions
- [ ] Event names descriptive dan consistent
- [ ] Tailwind classes organized dan tidak redundant
- [ ] No console.log yang tertinggal
- [ ] Components reusable dan tidak terlalu coupled
- [ ] Props validated dengan types
- [ ] Keys proper untuk v-for loops

## Resources

- [Vue 3 Documentation](https://vuejs.org/)
- [Inertia.js Documentation](https://inertiajs.com/)
- [Tailwind CSS Documentation](https://tailwindcss.com/)
- [TypeScript Documentation](https://www.typescriptlang.org/)
