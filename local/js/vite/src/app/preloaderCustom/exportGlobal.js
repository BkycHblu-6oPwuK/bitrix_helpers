import { usePreloader } from "./usePreloader.js";
import './index.js';

const { show, hide } = usePreloader();

window.showPreloader = show;
window.hidePreloader = hide;