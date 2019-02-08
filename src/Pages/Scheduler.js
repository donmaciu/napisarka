import React,{Component} from 'react';
import BigCalendar from 'react-big-calendar';
import { Dialog, Classes, Button, FormGroup, InputGroup } from '@blueprintjs/core';
import moment from 'moment';

const localizer = BigCalendar.momentLocalizer(moment);

class Scheduler extends Component {

    constructor(props) {
        super(props);

        this.state = {
            isEditor: false,
            event: null,
        }
    }

    render() {
        return (
            <div style={{height: '100vH'}}>
                <div className='p-3'>
                    <Button title='Dodaj napis' value='Dodaj napis' onClick={() => this.setState({isEditor: true})}>Dodan napis</Button>
                </div>
                <BigCalendar
                
                    localizer={localizer}
                    events={[]}
                    startAccessor="start"
                    endAccessor="end"
                />
                <Dialog icon='info-sign' title='Napis' isOpen={this.state.isEditor}>
                    <div className={Classes.DIALOG_BODY}>
                        <FormGroup
                            label='Napis'
                            labelFor='napis_box'
                        >
                            <InputGroup id='napis_box' placeholder='Wyświetlany napis'/>
                        </FormGroup>
                    </div>
                    <div className={Classes.DIALOG_FOOTER} style={{display: 'flex', flexDirection: 'row', justifyContent: 'space-evenly'}}>
                        <Button intent='none' title='Anuluj' value='Anuluj' onClick={() => this.setState({isEditor: false})}>Anuluj</Button>
                        <Button intent='primary' title='Zapisz' value='Zapisz' >Zapisz</Button>
                    </div>
                </Dialog>
            </div>
        )
    }
}

export default Scheduler;