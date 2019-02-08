import React,{Component} from 'react';
import BigCalendar from 'react-big-calendar';
import { Dialog, Classes, Button, FormGroup, InputGroup } from '@blueprintjs/core';
import { DateRangePicker } from '@blueprintjs/datetime';
import { CirclePicker } from 'react-color';
import moment from 'moment';
import 'moment/locale/pl';

const localizer = BigCalendar.momentLocalizer(moment);

const messages = {
    allDay: 'cały dzień',
    previous: 'poprzedni',
    next: 'następny',
    today: 'dziś',
    month: 'miesiąc',
    week: 'tydzień',
    day: 'dzień',
    agenda: 'agenda',
    date: 'data',
    time: 'czas',
    event: 'wydarzenie'
}

const fetchEvents = async (query) => {

    let objQ = {};

    if(query) {
        objQ = query;
    }

    let q = Object.keys(objQ).map(key => {
        return `${key}=${encodeURIComponent(objQ[key])}`;
    }).join('&');

    //console.log('qitem', q);

    const res = await fetch('/api/lista' + (q.length > 0 ? `?${q}` : ''), {
        headers: {
            'Content-type': 'application/json; charset=utf-8'
        }
    })

    if(res.status === 200) {
        return await res.json();
    } else {
        throw new Error('status_' + res.status);
    }
}

const postEvent = async (item) => {
    if(!item) {
        return item;
    }

    let url = '/api/lista';

    if(item.id) {
        url += `/${item.id}`;
    }

    const res = await fetch(url, {
        headers: {
            'Content-type': 'application/json; charset=utf8'
        },
        method: 'POST',
        body: JSON.stringify(item)
    })

    if(res.status === 200) {
        return await res.json();
    } else {
        throw new Error('status_' + res.status);
    }
}

const deleteEvent = async (id) => {
    const res = await fetch(`/api/lista/${id}`, {
        headers: {
            'Content-type': 'application/json; charset=utf8'
        },
        method: 'DELETE'
    })

    if(res.status === 200) {
        return;
    } else {
        throw new Error('status_' + res.status);
    }
}

class Scheduler extends Component {

    constructor(props) {
        super(props);

        this.state = {
            isEditor: false,
            event: {napis: null},
            rangeStart: null,
            rangeEnd: null,
            events: [],
            loading: false
        }
    }

    componentDidMount() {
        this.start();
    }

    start() {
        let rangeStart = this.state.rangeStart;
        let rangeEnd = this.state.rangeEnd;

        if(!rangeStart && !rangeEnd) {
            rangeStart = moment().startOf('month').valueOf();
            rangeEnd = moment().endOf('month').valueOf();
        }

        //console.log(rangeStart, rangeEnd)

        this.setState({rangeStart, rangeEnd}, () => this.downloadEvents());
    }

    changeRange(items) {
        if(items != null && items.start && items.end) {
            this.setState({rangeStart: moment(items.start).valueOf(), rangeEnd: moment(items.end).valueOf()}, () => this.downloadEvents());
        }
    }

    downloadEvents() {
        const { rangeStart, rangeEnd } = this.state;

        //console.log('dls');

        fetchEvents({sinceDate: rangeStart, toDate: rangeEnd})
        .then(res => {
            if(res instanceof Array) {

                //console.log('res', res);

                const evs = res.map(item => {
                    return {
                        title: item.napis,
                        start: new Date(item.sinceDate),
                        end: new Date(item.toDate),
                        resource: item
                    }
                })

                this.setState({events: evs});
            }
        })
        .catch(err => {
            console.log(err);
        })
    }

    postEvent() {
        const {event} = this.state;

        if(!event) return;

        this.setState({loading: true})

        postEvent(event)
        .then(res => {
            this.downloadEvents();
            this.setState({loading: false, isEditor: false})
        })
        .catch(err => {
            console.log(err);
            this.setState({loading: false})
        })
    }

    deleteEvent() {
        const {event} = this.state;

        if(!event) return;

        deleteEvent(event.id)
        .then(res => {
            this.downloadEvents();
            this.setState({isEditor: false})
        })
        .catch(err => {
            console.log(err);
        })
    }

    onDouble(slot) {
        if(slot && slot.action === 'doubleClick' && slot.slots.length === 1) {
            let time = slot.slots[0];

            if(!time) return;

            this.setState({event: {sinceDate: time.getTime(), toDate: time.getTime()}, isEditor: true})
        }
    }

    render() {
        const {event} = this.state;

        return (
            <div style={{height: '100vH', display: 'flex', flexDirection:'column'}}>
                <div className='p-3'>
                    <Button large icon='add' title='Dodaj napis' value='Dodaj napis' onClick={() => this.setState({event: {}, isEditor: true})}>Dodaj napis</Button>
                </div>
                <div style={{flex: 1}}>
                    <BigCalendar
                        messages={messages}
                        selectable={true}
                        onSelectSlot={slot => this.onDouble(slot)}
                        onRangeChange={this.changeRange.bind(this)}
                        localizer={localizer}
                        events={this.state.events}
                        startAccessor="start"
                        endAccessor="end"
                        onSelectEvent={(event) => {
                            if(event && event.resource) {
                                this.setState({event: event.resource, isEditor: true})
                            }
                        }}
                        eventPropGetter={(event) => {
                            if(event.resource && event.resource.color) {
                                return { style: {backgroundColor: event.resource.color}}
                            } else {
                                return {};
                            }
                        }}
                    />
                </div>
                
                <Dialog style={{width: 'initial'}} icon='info-sign' title='Napis' isOpen={this.state.isEditor} onClose={() => this.setState({isEditor: false})}>
                    <div className={Classes.DIALOG_BODY}>
                        <FormGroup
                            label='Napis'
                            labelFor='napis_box'
                        >
                            <InputGroup value={this.state.event.napis ? this.state.event.napis : ''} onChange={e => this.setState({event: {...event, napis: e.target.value}})} id='napis_box' placeholder='Wyświetlany napis'/>
                        </FormGroup>
                        <DateRangePicker
                            allowSingleDayRange={true}
                            timePrecision='minute'
                            value={[event.sinceDate ? new Date(event.sinceDate) : undefined, event.toDate ? new Date(event.toDate) : undefined]} 
                            onChange={item => this.setState({event: {...this.state.event, sinceDate: item[0] ? item[0].getTime() : undefined, toDate: item[1] ? item[1].getTime() : undefined}})}/>
                    
                        <div className='p-3' style={{display: 'flex', flexDirection: 'row', justifyContent: 'center'}}>
                            <CirclePicker 
                                color={ event.color }
                                onChangeComplete={color => this.setState({event: {...event, color: color.hex}})}
                            />
                        </div>
                        
                    </div>
                    <div className={Classes.DIALOG_FOOTER} style={{display: 'flex', flexDirection: 'row', justifyContent: 'space-evenly'}}>
                        <Button intent='none' title='Anuluj' value='Anuluj' onClick={() => this.setState({isEditor: false})}>Anuluj</Button>
                        {event && event.id ? <Button onClick={this.deleteEvent.bind(this)} intent='danger'>Usuń</Button> : undefined }
                        <Button loading={this.state.loading} disabled={!event.napis || !event.toDate || !event.sinceDate} intent='primary' title='Zapisz' value='Zapisz' onClick={() => this.postEvent()} >Zapisz</Button>
                    
                    </div>
                </Dialog>
            </div>
        )
    }
}

export default Scheduler;