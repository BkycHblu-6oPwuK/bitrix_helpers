import { createWebHistory, createRouter } from 'vue-router'

import Personal from './pages/Personal.vue'
import Orders from './pages/Orders.vue'
import Question from './pages/Question.vue'
import OrderDetail from './pages/OrderDetail.vue'
import Dressings from './pages/Dressings.vue'
import DressingDetail from './pages/DressingDetail.vue'

const routes = [
  { path: '/account/', component: Personal, name: 'account' },
  { path: '/account/orders/', component: Orders, name: 'orders' },
  { path: '/account/orders/:id', component: OrderDetail, name: 'order' },
  { path: '/account/dressings/', component: Dressings, name: 'dressings' },
  { path: '/account/dressings/:id', component: DressingDetail, name: 'dressing' },
  { path: '/account/questions/', component: Question, name: 'questions' },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  linkActiveClass: 'profile__info-tab_active'
})

export default router;