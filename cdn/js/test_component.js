class TC extends React.Component{
    render(){
        return (
            <div className="shopping-list">
            <h1>Shopping List for {this.props.name}</h1>
                                  <ul>
                                  <li><InstagramLink/></li>
                                  <li>WhatsApp</li>
                                  <li>Oculus</li>
                                  </ul>
                                  </div>
        )
    }
}

class InstagramLink extends React.Component{
    render(){
        return(
            <p>Test</p>
        )
    }
}
ReactDOM.render(<TC />, DOMContainer);