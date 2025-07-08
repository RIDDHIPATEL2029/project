import streamlit as st
import hashlib
from datetime import datetime
import json
import os
from fpdf import FPDF

# Path to the JSON file
JSON_FILE = "accounts.json"

# Custom CSS for styling
st.markdown(
    """
    <style>
    .stButton button {
        background-color: #4CAF50;
        color: white;
        border-radius: 5px;
        padding: 10px 24px;
        font-size: 16px;
    }
    .stTextInput input {
        padding: 10px;
        border-radius: 5px;
        border: 1px solid #ccc;
    }
    .stSelectbox select {
        padding: 10px;
        border-radius: 5px;
        border: 1px solid #ccc;
    }
    .stMarkdown h1 {
        color: #4CAF50;
    }
    </style>
    """,
    unsafe_allow_html=True,
)

# Load accounts from JSON file
def load_accounts():
    if not os.path.exists(JSON_FILE):
        return {}
    try:
        with open(JSON_FILE, "r") as file:
            if os.path.getsize(JSON_FILE) == 0:
                return {}
            return json.load(file)
    except json.JSONDecodeError:
        return {}

# Save accounts to JSON file
def save_accounts(accounts):
    with open(JSON_FILE, "w") as file:
        json.dump(accounts, file, indent=4)

# Function to create an account
def create_account(username, password, account_type):
    accounts = load_accounts()
    if username in accounts:
        return "❌ Username already exists!"
    hashed_password = hashlib.sha256(password.encode()).hexdigest()
    accounts[username] = {
        "password": hashed_password,
        "account_type": account_type,
        "balance": 0.0,
        "transactions": [],
        "overdraft_limit": -500 if account_type == "Checking" else 0,
        "monthly_spending": 0.0,
        "spending_limit": None
    }
    save_accounts(accounts)
    return f"✅ Account created for {username}!"

# Function to deposit money
def deposit(username, amount):
    if amount <= 0:
        return "❌ Invalid amount!"
    accounts = load_accounts()
    if username not in accounts:
        return "❌ Account not found!"
    accounts[username]["balance"] += amount
    transaction = f"{datetime.now()}: Deposited £{amount:.2f}"
    accounts[username]["transactions"].append(transaction)
    save_accounts(accounts)
    return f"✅ Deposited £{amount:.2f} successfully!"

# Function to withdraw money
def withdraw(username, amount):
    if amount <= 0:
        return "❌ Invalid amount!"
    accounts = load_accounts()
    if username not in accounts:
        return "❌ Account not found!"
    balance = accounts[username]["balance"]
    overdraft_limit = accounts[username]["overdraft_limit"]
    if balance - amount < overdraft_limit:
        return "❌ Withdrawal denied: Overdraft limit exceeded!"
    accounts[username]["balance"] -= amount
    transaction = f"{datetime.now()}: Withdrew £{amount:.2f}"
    accounts[username]["transactions"].append(transaction)
    accounts[username]["monthly_spending"] += amount
    save_accounts(accounts)
    return f"✅ Withdrew £{amount:.2f} successfully!"

# Function to generate a mini-statement
def generate_statement(username):
    accounts = load_accounts()
    if username not in accounts:
        return "❌ Account not found!"
    transactions = accounts[username]["transactions"]
    statement = "\n".join(transactions[-5:])
    return statement

# Function to generate a PDF statement
def generate_pdf_statement(username):
    accounts = load_accounts()
    if username not in accounts:
        return None

    # Create a PDF object
    pdf = FPDF()
    pdf.add_page()
    pdf.set_font("Arial", size=12)

    # Add a title
    pdf.cell(200, 10, txt="Bank Account Statement", ln=True, align="C")
    pdf.ln(10)

    # Add account details
    pdf.cell(200, 10, txt=f"Account Holder: {username}", ln=True)
    pdf.cell(200, 10, txt=f"Account Type: {accounts[username]['account_type']}", ln=True)
    pdf.cell(200, 10, txt=f"Current Balance: £{accounts[username]['balance']:.2f}", ln=True)
    pdf.ln(10)

    # Add transaction history
    pdf.cell(200, 10, txt="Transaction History:", ln=True)
    for transaction in accounts[username]["transactions"]:
        pdf.cell(200, 10, txt=transaction, ln=True)

    # Save the PDF to a file
    pdf_file = f"{username}_statement.pdf"
    pdf.output(pdf_file)
    return pdf_file

# Function to calculate interest
def calculate_interest(username, months):
    accounts = load_accounts()
    if username not in accounts:
        return "❌ Account not found!"
    account = accounts[username]
    if account["account_type"] != "Savings":
        return "❌ Interest only applies to savings accounts!"
    rate = 0.02 / 12  # 2% annual interest, calculated monthly
    balance = account["balance"]
    interest = balance * (1 + rate) ** months - balance
    return f"💰 Interest over {months} months: £{interest:.2f}"

# Streamlit UI
st.title("🏦 Bank Account Simulator")
st.markdown("Welcome to the Bank Account Simulator! Manage your accounts, perform transactions, and track your finances with ease.")

# Sidebar for navigation
st.sidebar.header("📜 Navigation")
page = st.sidebar.radio("Go to", ["Create Account", "Deposit", "Withdraw", "View Statement", "Calculate Interest"])

if page == "Create Account":
    st.header("📝 Create Account")
    col1, col2 = st.columns(2)
    with col1:
        username = st.text_input("👤 Username")
    with col2:
        password = st.text_input("🔑 Password", type="password")
    account_type = st.selectbox("📄 Account Type", ["Savings", "Checking", "Business"])
    if st.button("Create Account"):
        result = create_account(username, password, account_type)
        st.success(result)

elif page == "Deposit":
    st.header("💵 Deposit Money")
    username = st.text_input("👤 Username")
    amount = st.number_input("💰 Amount", min_value=0.01)
    if st.button("Deposit"):
        result = deposit(username, amount)
        st.success(result)

elif page == "Withdraw":
    st.header("💸 Withdraw Money")
    username = st.text_input("👤 Username")
    amount = st.number_input("💰 Amount", min_value=0.01)
    if st.button("Withdraw"):
        result = withdraw(username, amount)
        st.success(result)

elif page == "View Statement":
    st.header("📊 View Mini-Statement")
    username = st.text_input("👤 Username")
    if st.button("Generate Statement"):
        statement = generate_statement(username)
        st.text_area("📜 Mini-Statement", statement)

    # Add a button to generate and download the PDF statement
    if st.button("📄 Print Statement (PDF)"):
        pdf_file = generate_pdf_statement(username)
        if pdf_file:
            with open(pdf_file, "rb") as file:
                st.download_button(
                    label="Download PDF Statement",
                    data=file,
                    file_name=pdf_file,
                    mime="application/pdf",
                )
        else:
            st.error("❌ Account not found!")

elif page == "Calculate Interest":
    st.header("📈 Calculate Interest")
    with st.expander("ℹ️ How it works"):
        st.write("Interest is calculated at 2% annually, compounded monthly. This feature is only available for savings accounts.")
    username = st.text_input("👤 Username")
    months = st.number_input("📅 Months", min_value=1, max_value=120)
    if st.button("Calculate"):
        result = calculate_interest(username, months)
        st.success(result)