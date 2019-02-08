import polyfill from 'babel-polyfill';
import React,{Component} from 'react';
import ReactDOM from 'react-dom';

import Scheduler from './Pages/Scheduler';

class App extends Component {
    render() {
        return (
            <div>
                <Scheduler />
            </div>
        )
    }
}

const appDiv = document.getElementById('app');

if(appDiv) {
    ReactDOM.render((<App />), appDiv);
}
