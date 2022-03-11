// jshint es3: false
// jshint esversion: 6

import { isProd, devTasks, prodTasks } from '../../config';

const taskList = isProd ? prodTasks : devTasks;

export default taskList;
