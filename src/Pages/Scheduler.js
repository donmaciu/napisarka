import React,{Component} from 'react';
import BigCalendar from 'react-big-calendar';
import moment from 'moment';

const localizer = BigCalendar.momentLocalizer(moment);

class Scheduler extends Component {

    render() {
        return (
            <div style={{height: '80vH'}}>
                <BigCalendar
                    localizer={localizer}
                    events={[]}
                    startAccessor="start"
                    endAccessor="end"
                />
            </div>
        )
    }
}

export default Scheduler;