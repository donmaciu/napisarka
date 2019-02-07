import React,{Component} from 'react';
import ReactDOM from 'react-dom';

import Scheduler from './Pages/Scheduler';

class App extends Component {
    render() {
        return (
            <div>
                <h2>Working :P</h2>
                <Scheduler />
            </div>
        )
    }
}

const appDiv = document.getElementById('app');

if(appDiv) {
    ReactDOM.render((<App />), appDiv);
}
